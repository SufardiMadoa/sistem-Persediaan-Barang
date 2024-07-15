<?php
require 'function.php';
$query = "SELECT * FROM penjualan";
$result = $conn->query($query);
$query = "SELECT p.kode_penjualan, p.tgl_penjualan, p.total_penjualan, dp.kode_barang, b.nama_barang, dp.harga_penjualan, dp.jumlah_penjualan 
          FROM penjualan p
          JOIN detail_penjualan dp ON p.kode_penjualan = dp.kode_penjualan
          JOIN barang b ON dp.kode_barang = b.kode_barang";
$result = $conn->query($query); 

$barangQuery = "SELECT kode_barang, harga_beli, nama_barang, harga_jual FROM barang";
$barangResult = mysqli_query($conn, $barangQuery);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan'])) {
    $kode_penjualan = $_POST['kode_penjualan'];
    $tgl_penjualan = $_POST['tgl_penjualan'];

    $kode_barang = isset($_POST['kode_barang']) ? $_POST['kode_barang'] : [];
    $total_rows = count($kode_barang);
    $total = $_POST['total_penjualan'];
    $total_penjualan = $total * $total_rows;
    
    
    $harga_jual = isset($_POST['harga_jual']) ? $_POST['harga_jual'] : [];
    // $jumlah_penjualan = isset($_POST['total_penjualan']) ? $_POST['total_penjualan'] : [];
    $total_bayar = isset($_POST['total_bayar']) ? $_POST['total_bayar'] : [];

    $total_bayar = !empty($total_bayar) ? array_sum($total_bayar) : 0; // Calculate total bayar for all items

    // Begin transaction
    $conn->begin_transaction();

    try {
        foreach ($kode_barang as $i => $kode) {
            $check_stock_query = "SELECT jumlah_barang FROM barang WHERE kode_barang = ?";
            $stmt_check_stock = $conn->prepare($check_stock_query);
            $stmt_check_stock->bind_param("s", $kode);
            $stmt_check_stock->execute();
            $stmt_check_stock->bind_result($stok_barang);
            $stmt_check_stock->fetch();
    
            if ($stok_barang < $total) {
                // Stock is insufficient, display alert and prevent sale
                echo "<script>alert('Stock barang {$kode} tidak mencukupi untuk penjualan ini');</script>";
                $stmt_check_stock->free_result(); // Free result set
                $conn->rollback(); // Rollback transaction
                exit(); // or redirect to prevent further processing
            }
    
            $stmt_check_stock->free_result(); // Free result set after each iteration
        }
    
        // Insert into penjualan table
        $query_penjualan = "INSERT INTO penjualan (kode_penjualan, tgl_penjualan, total_penjualan, total_bayar) VALUES (?, ?, ?, ?)";
        $stmt_penjualan = $conn->prepare($query_penjualan);
        $stmt_penjualan->bind_param("ssss", $kode_penjualan, $tgl_penjualan, $total_penjualan, $total_bayar);
        $stmt_penjualan->execute();
    
        // Insert into detail_penjualan table
        foreach ($kode_barang as $i => $kode) {
            // Generate a unique kode_det_penjualan
            $kode_det_penjualan = $kode_penjualan . '-' . ($i + 1);
            $jumlah = $total;
            $harga = isset($harga_jual[$i]) ? $harga_jual[$i] : 0;
            $query_detail = "INSERT INTO detail_penjualan (kode_det_penjualan, kode_penjualan, kode_barang, jumlah_penjualan, harga_penjualan) VALUES (?, ?, ?, ?, ?)";
            $stmt_detail = $conn->prepare($query_detail);
            $stmt_detail->bind_param("sssii", $kode_det_penjualan, $kode_penjualan, $kode, $jumlah, $harga);
            $stmt_detail->execute();

            $update_stock_query = "UPDATE barang SET jumlah_barang = jumlah_barang - ? WHERE kode_barang = ?";
            $stmt_update_stock = $conn->prepare($update_stock_query);
            $stmt_update_stock->bind_param("is", $jumlah, $kode);
            $stmt_update_stock->execute();
        }
    
        // Commit transaction
        $conn->commit();
        
        // Redirect or display success message
        header("Location: penjualan.php?success=1");
        exit();
    
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        throw $exception;
    }
}
// Fetch pembelian data for the table
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '1970-01-01';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$pembelianQuery = "
    SELECT p.kode_pembelian, p.tgl_pembelian, p.id_pemasok, p.total_pembelian, b.nama_barang, d.harga_pembelian
    FROM pembelian p
    JOIN detail_pembelian d ON p.kode_pembelian = d.kode_pembelian
    JOIN barang b ON d.kode_barang = b.kode_barang
    WHERE p.tgl_pembelian BETWEEN ? AND ?
";
$stmt = mysqli_prepare($conn, $pembelianQuery);
mysqli_stmt_bind_param($stmt, "ss", $startDate, $endDate);
mysqli_stmt_execute($stmt);
$pembelianResult = mysqli_stmt_get_result($stmt);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Admin - Gusniar Kayu</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="index.php">UD Gusniar Kayu</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <a class="nav-link" href="pengguna.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Data Pengguna
                        </a>
                        <a class="nav-link" href="pemasok.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Data Pemasok
                        </a>
                        <a class="nav-link" href="barang.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Data Barang
                        </a>
                        <a class="nav-link" href="pembelian.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Data Pembelian
                        </a>
                        <a class="nav-link" href="penjualan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Data Penjualan
                        </a>
                        <a class="nav-link" href="persediaan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Kartu Persediaan
                        </a>
                        <a class="nav-link" href="index.html">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Kartu Stok Gudang
                        </a>
                        <a class="nav-link" href="logout.php">
                            Logout
                        </a>
                    </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Laporan Penjualan</h1>
                    <div class="card mb-4">
                        <div class="card-header">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                                + Tambah
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Filter Tanggal -->
                            <form method="GET" class="mb-4">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>" required>
                                    </div>
                                </div>
                            </form>
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Kode Penjualan</th>
                                        <th>Tanggal Penjualan</th>
                                        <th>Total Penjualan</th>
                                        <th>Nama Barang</th>
                                        <th>Harga Jual</th>
                                        <th>Total Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while ($row = $result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo $row['kode_penjualan']; ?></td>
                                        <td><?php echo $row['tgl_penjualan']; ?></td>
                                        <td><?php echo $row['jumlah_penjualan']; ?></td>
                                        <td><?php echo $row['nama_barang']; ?></td>
                                        <td><?php echo $row['harga_penjualan']; ?></td>
                                        <td><?php echo $row['jumlah_penjualan'] * $row['harga_penjualan']; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-success" onclick="print()">Cetak</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Virga 2024</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>
<!-- The Modal -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Input Penjualan</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="kode_pembelian" class="form-label">Kode Penjualan</label>
                        <input type="text" class="form-control" id="kode_pembelian" name="kode_penjualan" required>
                    </div>
                    <div class="mb-3">
                        <label for="tgl_pembelian" class="form-label">Tanggal Penjualan</label>
                        <input type="date" class="form-control" id="tgl_pembelian" name="tgl_penjualan" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_pemasok" class="form-label">Total Penjualan</label>
                        <input type="text" class="form-control" id="id_pemasok" name="total_penjualan" required>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Harga Jual</th>
                                    <th>Total Bayar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">
                            <tr>
                                                    <td>
                                                        <select name="kode_barang[]" class="form-select" required>
                                                            <option value="">Pilih Barang</option>
                                                            <?php mysqli_data_seek($barangResult, 0); // reset cursor ?>
                                                            <?php while ($row = mysqli_fetch_assoc($barangResult)) { ?>
                                                                <option value="<?= $row['kode_barang'] ?>"><?= $row['nama_barang'] ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="number" name="harga_jual[]" class="form-control harga-jual" required></td>
                                                    <td><input type="text" name="total_bayar[]" class="form-control total-bayar" readonly required></td>
                                                    <td><button type="button" class="btn btn-danger remove-row">Hapus</button></td>
                                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success" id="addRow">Tambah Baris</button>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" name="simpan">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
   function calculateTotalBayar(row) {
            const hargaJual = parseFloat(row.querySelector('.harga-jual').value) || 0;
            const jumlahPenjualan = parseFloat(row.querySelector('.jumlah-penjualan').value) || 0;
            const totalBayar = row.querySelector('.total-bayar');
            totalBayar.value = hargaJual * jumlahPenjualan;
        }

        // document.addEventListener('input', function(e) {
        //     if (e.target.classList.contains('harga-jual') || e.target.classList.contains('jumlah-penjualan')) {
        //         const row = e.target.closest('tr');
        //         calculateTotalBayar(row);
        //     }
        // });
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('harga-jual') || e.target.classList.contains('jumlah-penjualan')) {
                const row = e.target.closest('tr');
                calculateTotalBayar(row);
                const totalPenjualanInput = document.getElementById('total_penjualan');
                const totalBayarInputs = document.querySelectorAll('.total-bayar');
                let totalPenjualan = 0;
                totalBayarInputs.forEach(input => {
                    totalPenjualan += parseFloat(input.value);
                });
                totalPenjualanInput.value = totalPenjualan;
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
                const totalPenjualanInput = document.getElementById('total_penjualan');
                const totalBayarInputs = document.querySelectorAll('.total-bayar');
                let totalPenjualan = 0;
                totalBayarInputs.forEach(input => {
                    totalPenjualan += parseFloat(input.value);
                });
                totalPenjualanInput.value = totalPenjualan;
            }
        });


    document.getElementById('addRow').addEventListener('click', function () {
        var table = document.getElementById('itemRows');
        var row = table.insertRow();
        row.innerHTML = `
            <td>
                <select name="kode_barang[]" class="form-select" required>
                    <option value="">Pilih Barang</option>
                    <?php mysqli_data_seek($barangResult, 0); // reset cursor ?>
                    <?php while ($row = mysqli_fetch_assoc($barangResult)) { ?>
                        <option value="<?= $row['kode_barang'] ?>"><?= $row['nama_barang'] ?></option>
                    <?php } ?>
                </select>
            </td>
            <td><input type="number" name="harga_jual[]" class="form-control" required></td>
            <td><input type="text" name="total_bayar[]" class="form-control total-bayar" readonly required></td>
            <td><button type="button" class="btn btn-danger remove-row">Hapus</button></td>
        `;
    });

    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
        }
    });
</script>
</html>

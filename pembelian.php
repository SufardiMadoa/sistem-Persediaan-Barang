


<?php
require 'function.php';

// Fetch pemasok data
$pemasokQuery = "SELECT id_pemasok, nama_pemasok FROM pemasok";
$pemasokResult = mysqli_query($conn, $pemasokQuery);

// Fetch barang data
$barangQuery = "SELECT * FROM barang";
$barangResult = mysqli_query($conn, $barangQuery);

// Handle form submission
if (isset($_POST['simpan'])) {
    $kode_pembelian = $_POST['kode_pembelian'];
    $tgl_pembelian = $_POST['tgl_pembelian'];
    $id_pemasok = $_POST['id_pemasok'];
    $total_pembelian = $_POST['total_pembelian'];
    $kode_barang = $_POST['kode_barang'];
    $harga_beli = $_POST['harga_beli'];

    // Check if $id_pemasok exists in pemasok table
    $checkPemasokQuery = "SELECT id_pemasok FROM pemasok WHERE id_pemasok = ?";
    $stmtCheckPemasok = mysqli_prepare($conn, $checkPemasokQuery);
    mysqli_stmt_bind_param($stmtCheckPemasok, "s", $id_pemasok);
    mysqli_stmt_execute($stmtCheckPemasok);
    mysqli_stmt_store_result($stmtCheckPemasok);

    if (mysqli_stmt_num_rows($stmtCheckPemasok) > 0) {
        // Proceed with insertion into pembelian table
        $insertPembelian = "INSERT INTO pembelian (kode_pembelian, tgl_pembelian, id_pemasok, total_pembelian, kode_barang, harga_beli) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertPembelian);

        for ($i = 0; $i < count($total_pembelian); $i++) {
            $kode_barang_item = $kode_barang[$i];
            $harga_pembelian_item = $harga_beli[$i];
            $total_pembelian_item = $total_pembelian[$i];

            // Execute pembelian insertion
            mysqli_stmt_bind_param($stmt, "ssssss", $kode_pembelian, $tgl_pembelian, $id_pemasok, $total_pembelian_item, $kode_barang_item, $harga_pembelian_item);
            mysqli_stmt_execute($stmt);

            // Insert into detail_pembelian table
            $kode_det_pembelian = $kode_pembelian . '-' . ($i + 1);
            $insertDetail = "INSERT INTO detail_pembelian (kode_det_pembelian, kode_pembelian, kode_barang, jumlah_pembelian, harga_pembelian) VALUES (?, ?, ?, ?, ?)";
            $stmtDetail = mysqli_prepare($conn, $insertDetail);
            mysqli_stmt_bind_param($stmtDetail, "sssis", $kode_det_pembelian, $kode_pembelian, $kode_barang_item, $total_pembelian_item, $harga_pembelian_item);
            mysqli_stmt_execute($stmtDetail);
            mysqli_stmt_close($stmtDetail);


            $kode_persediaan = $kode_pembelian . '-K-' . ($i + 1);
            $insertKartu = "INSERT INTO kartu_persediaan (kode_persediaan, tanggal_persediaan, kode_det_pembelian, unit_masuk, harga_masuk, total_masuk) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtKartu = mysqli_prepare($conn, $insertKartu);
            $total_masuk = $total_pembelian_item * $harga_pembelian_item;
            mysqli_stmt_bind_param($stmtKartu, "sssiii", $kode_persediaan, $tgl_pembelian, $kode_det_pembelian, $total_pembelian_item, $harga_pembelian_item, $total_masuk);
            mysqli_stmt_execute($stmtKartu);
            mysqli_stmt_close($stmtKartu);
            // Update stock barang
            // $updateStokQuery = "UPDATE barang SET jumlah_barang = jumlah_barang + ? WHERE kode_barang = ?";
            // $stmtUpdateStok = mysqli_prepare($conn, $updateStokQuery);
            // mysqli_stmt_bind_param($stmtUpdateStok, "is", $total_pembelian_item, $kode_barang_item);
            // mysqli_stmt_execute($stmtUpdateStok);
            // mysqli_stmt_close($stmtUpdateStok);
 // Fetch current data for the item from barang table
 $fetchBarangQuery = "SELECT jumlah_barang, harga_beli FROM barang WHERE kode_barang = ?";
 $stmtFetchBarang = mysqli_prepare($conn, $fetchBarangQuery);
 mysqli_stmt_bind_param($stmtFetchBarang, "s", $kode_barang_item);
 mysqli_stmt_execute($stmtFetchBarang);
 $resultFetchBarang = mysqli_stmt_get_result($stmtFetchBarang);
 $rowBarang = mysqli_fetch_assoc($resultFetchBarang);
 mysqli_stmt_close($stmtFetchBarang);

 $current_jumlah_barang = $rowBarang['jumlah_barang'];
 $current_harga_beli = $rowBarang['harga_beli'];

 // Calculate new stock and average price
 $jumlah_barang_baru = $current_jumlah_barang + $total_pembelian_item;
 $modal_awal = $current_jumlah_barang * $current_harga_beli;
 $modal_pembelian = $total_pembelian_item * $harga_pembelian_item;
 $modal_awal_baru = $modal_awal + $modal_pembelian;
 $harga_rata_rata_baru = $modal_awal_baru / $jumlah_barang_baru;

 // Update barang table with new average price
 $updateBarangQuery = "UPDATE barang SET jumlah_barang = ?, harga_beli = ?, harga_jual = ? WHERE kode_barang = ?";
 $stmtUpdateBarang = mysqli_prepare($conn, $updateBarangQuery);
 mysqli_stmt_bind_param($stmtUpdateBarang, "iiis", $jumlah_barang_baru, $harga_rata_rata_baru, $harga_rata_rata_baru, $kode_barang_item);
 mysqli_stmt_execute($stmtUpdateBarang);
 mysqli_stmt_close($stmtUpdateBarang);



            // lakukan menghitung rata rata disini
            // $totalbayar = $total_pembelian_item * $harga_pembelian_item;
            // $modalawal = $modal+$totalbayar;
            // $modaljual = "jumlah_barang" : $modalawal;
        }

        // Close statement for pembelian
        mysqli_stmt_close($stmt);

        // Display success message or redirect
        echo '<div class="alert alert-success" role="alert">Data pembelian berhasil dimasukkan!</div>';
    } else {
        // Handle invalid pemasok ID
        echo '<div class="alert alert-danger" role="alert">ID Pemasok tidak valid.</div>';
    }

    // Close statement for pemasok check
    mysqli_stmt_close($stmtCheckPemasok);
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
$stmtPembelian = mysqli_prepare($conn, $pembelianQuery);
mysqli_stmt_bind_param($stmtPembelian, "ss", $startDate, $endDate);
mysqli_stmt_execute($stmtPembelian);
$pembelianResult = mysqli_stmt_get_result($stmtPembelian);
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
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="index.php">UD Gusniar Kayu</a>
        <!-- Sidebar Toggle-->
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
                        <a class="nav-link" href="#">
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
                    <h1 class="mt-4">Laporan Pembelian</h1>
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
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary mt-4">Filter</button>
                                    </div>
                                </div>
                            </form>
                            <table id="datatablesSimple" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Kode Pembelian</th>
                                        <th>Tanggal Pembelian</th>
                                        <th>ID Pemasok</th>
                                        <th>Total Pembelian</th>
                                        <th>Nama Barang</th>
                                        <th>Harga Beli</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while ($row = mysqli_fetch_assoc($pembelianResult)) { ?>
                                    <tr>
                                        <td><?= $row['kode_pembelian'] ?></td>
                                        <td><?= $row['tgl_pembelian'] ?></td>
                                        <td><?= $row['id_pemasok'] ?></td>
                                        <td><?= $row['total_pembelian'] ?></td>
                                        <td><?= $row['nama_barang'] ?></td>
                                        <td><?= $row['harga_pembelian'] ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <div class="card-body">
                                <!-- Tombol cetak laporan -->
                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-success" onclick="cetakLaporan()">Cetak</button>
                                </div>
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
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
    <script>
        function cetakLaporan() {
            window.print();
        }
    </script>
</body>
<!-- The Modal -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Input Pembelian</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- Modal body -->
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_pembelian" class="form-label">Kode Pembelian</label>
                        <input type="text" class="form-control" id="kode_pembelian" name="kode_pembelian" required>
                    </div>
                    <div class="mb-3">
                        <label for="tgl_pembelian" class="form-label">Tanggal Pembelian</label>
                        <input type="date" class="form-control" id="tgl_pembelian" name="tgl_pembelian" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_pemasok" class="form-label">ID Pemasok</label>
                        <select class="form-select" id="id_pemasok" name="id_pemasok" required>
                            <option value="">Pilih Pemasok</option>
                            <?php mysqli_data_seek($pemasokResult, 0); // reset cursor ?>
                            <?php while ($row = mysqli_fetch_assoc($pemasokResult)) { ?>
                                <option value="<?= $row['id_pemasok'] ?>"> <?= $row['nama_pemasok'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Total Pembelian</th>
                                    <th>Nama Barang</th>
                                    <th>Harga Beli</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">
                                <tr>
                                    <td><input type="text" name="total_pembelian[]" class="form-control" maxlength="5" required></td>
                                    <td>
                                        <select name="kode_barang[]" class="form-select" required>
                                            <option value="">Pilih Barang</option>
                                            <?php mysqli_data_seek($barangResult, 0); // reset cursor ?>
                                            <?php while ($row = mysqli_fetch_assoc($barangResult)) { ?>
                                                <option value="<?= $row['kode_barang'] ?>"><?= $row['nama_barang'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="harga_beli[]" class="form-control"  required></td>
                                    <td><button type="button" class="btn btn-danger remove-row">Hapus</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success" id="addRow">Tambah Baris</button>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" name="simpan">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('addRow').addEventListener('click', function() {
    var table = document.getElementById('itemRows');
    var row = table.insertRow();
    row.innerHTML = `
            <td><input type="text" name="total_pembelian[]" class="form-control" maxlength="5" required></td>
            <td>
                <select name="kode_barang[]" class="form-control" required>
                    <option value="">Pilih Barang</option>
                    <?php mysqli_data_seek($barangResult, 0); // reset cursor ?>
                    <?php while ($row = mysqli_fetch_assoc($barangResult)) { ?>
                        <option value="<?= $row['kode_barang'] ?>"><?= $row['nama_barang'] ?></option>
                    <?php } ?>
                </select>
            </td>
             <td><input type="text" name="harga_beli[]" class="form-control"  required></td>
            <td><button type="button" class="btn btn-danger remove-row">Hapus</button></td>
        `;
});

document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
    }
});



$(document).ready(function() {
    $('#product').change(function() {
        var kodeBarang = $(this).val();
        if (kodeBarang) {
            $.ajax({
                url: 'get_details.php',
                type: 'POST',
                data: {
                    kode_barang: kodeBarang
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    $('#harga_beli').val(data.harga_beli);
                }
            });
        } else {
            $('#harga_beli').val('');
        }
    });
});
</script>
</html>

<?php
require 'function.php';
$barangQuery = "SELECT kode_barang, harga_beli, nama_barang FROM barang";
$barangResult = mysqli_query($conn, $barangQuery);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang = $_POST['kode_barang'];
    $stok_awal = $_POST['stok_awal'];
    $harga_awal = $_POST['harga_awal'];
    $kode_det_pembelian = $_POST['kode_det_pembelian']; // Assuming this is obtained from form input
    
    // Insert initial stock into kartu_persediaan based on detail_pembelian
    $insertQuery = "INSERT INTO kartu_persediaan (tanggal_persediaan, kode_det_pembelian, unit_masuk, harga_masuk, total_masuk, unit_persediaan, harga_persediaan, total_persediaan)
                    SELECT CURDATE(), '$kode_det_pembelian', dp.jumlah_pembelian, dp.harga_pembelian, dp.jumlah_pembelian * dp.harga_pembelian, dp.jumlah_pembelian, dp.harga_pembelian, dp.jumlah_pembelian * dp.harga_pembelian
                    FROM detail_pembelian dp
                    WHERE dp.kode_det_pembelian = '$kode_det_pembelian'";
    
    $result = mysqli_query($conn, $insertQuery);
    if (!$result) {
        echo "Error: " . mysqli_error($conn);
    }
}

// Mengambil data pembelian
$pembelianQuery = "SELECT dp.kode_barang, dp.jumlah_pembelian, dp.harga_pembelian 
                   FROM detail_pembelian dp
                   JOIN pembelian p ON dp.kode_pembelian = p.kode_pembelian";
$pembelianResult = mysqli_query($conn, $pembelianQuery);

// Mengambil data penjualan
$penjualanQuery = "SELECT dp.kode_barang, dp.jumlah_penjualan, dp.harga_penjualan 
                   FROM detail_penjualan dp
                   JOIN penjualan p ON dp.kode_penjualan = p.kode_penjualan";
$penjualanResult = mysqli_query($conn, $penjualanQuery);

// Inisialisasi variabel untuk persediaan dan harga rata-rata
$persediaan = [];
$averagePrice = [];
$totalStock = [];

// Mengambil data kartu persediaan dari database
$kartuQuery = "SELECT * FROM kartu_persediaan";
$kartuResult = mysqli_query($conn, $kartuQuery);

while ($row = mysqli_fetch_assoc($kartuResult)) {
    $kode_barang = $row['kode_persediaan'];
    $unit_masuk = $row['unit_masuk'];
    $harga_masuk = $row['harga_masuk'];
    $unit_keluar = $row['unit_keluar'];

    if (!isset($persediaan[$kode_barang])) {
        $persediaan[$kode_barang] = 0;
        $averagePrice[$kode_barang] = 0;
        $totalStock[$kode_barang] = 0;
    }

    $totalCost = $averagePrice[$kode_barang] * $persediaan[$kode_barang] + $harga_masuk * $unit_masuk;
    $persediaan[$kode_barang] += $unit_masuk - $unit_keluar;
    $totalStock[$kode_barang] += $unit_masuk - $unit_keluar;
    $averagePrice[$kode_barang] = $totalCost / $totalStock[$kode_barang];
}

// Memperbarui persediaan berdasarkan data pembelian
while ($row = mysqli_fetch_assoc($pembelianResult)) {
    $kode_barang = $row['kode_barang'];
    $jumlah = $row['jumlah_pembelian'];
    $harga_beli = $row['harga_pembelian'];

    if (!isset($persediaan[$kode_barang])) {
        $persediaan[$kode_barang] = 0;
        $averagePrice[$kode_barang] = 0;
    }

    $totalCost = $averagePrice[$kode_barang] * $persediaan[$kode_barang] + $harga_beli * $jumlah;
    $persediaan[$kode_barang] += $jumlah;
    $averagePrice[$kode_barang] = $totalCost / $persediaan[$kode_barang];
}

// Memperbarui persediaan berdasarkan data penjualan
while ($row = mysqli_fetch_assoc($penjualanResult)) {
    $kode_barang = $row['kode_barang'];
    $jumlah = $row['jumlah_penjualan'];
    $harga_jual = $row['harga_penjualan'];

    if (!isset($persediaan[$kode_barang])) {
        $persediaan[$kode_barang] = 0;
        $averagePrice[$kode_barang] = 0;
    }

    $persediaan[$kode_barang] -= $jumlah;
}
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
    <script>
        // Menampilkan data modal awal pada tabel
        window.onload = function() {
            var modalAwalData = JSON.parse(localStorage.getItem("modalAwal")) || {};
            var tableBody = document.querySelector("#datatablesSimple tbody");
            var row = tableBody.insertRow();

            row.insertCell().textContent = "Modal Awal";
            row.insertCell().textContent = "";
            row.insertCell().textContent = "";
            row.insertCell().textContent = "";
            row.insertCell().textContent = "";
            row.insertCell().textContent = "";
            row.insertCell().textContent = "";
            row.insertCell().textContent = modalAwalData['<?php echo $kode_barang; ?>'].stok_awal;
            row.insertCell().textContent = modalAwalData['<?php echo $kode_barang; ?>'].harga_awal;
            row.insertCell().textContent = modalAwalData['<?php echo $kode_barang; ?>'].stok_awal * modalAwalData['<?php echo $kode_barang; ?>'].harga_awal;
        };
    </script>
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
                            <form method="POST">
                                <div class="form-inline">
                                    <label class="mr-2" for="kode_barang">Kode Barang:</label>
                                    <select name="kode_barang" class="form-select" required>
                                        <option value="">Pilih Barang</option>
                                        <?php mysqli_data_seek($barangResult, 0); // reset cursor ?>
                                        <?php while ($row = mysqli_fetch_assoc($barangResult)) { ?>
                                            <option value="<?= $row['kode_barang'] ?>"><?= $row['kode_barang'] ?> - <?= $row['nama_barang'] ?></option>
                                        <?php } ?>
                                    </select>
                                    <label class="mr-2" for="stok_awal">Stok Awal:</label>
                                    <input type="number" name="stok_awal" class="form-control" required>
                                    <label class="mr-2" for="harga_awal">Harga Awal:</label>
                                    <input type="number" name="harga_awal" class="form-control" required>
                                    <button type="submit" class="btn btn-success">Simpan</button>
                                </div>
                            </form>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="mb-4">
                                <div class="form-inline">
                                    <label class="mr-2" for="nama_barang">Nama Barang:</label>
                                    <input type="text" name="nama_barang" class="form-control" required>
                                    <button type="submit" class="btn btn-primary">Cari</button>
                                </div>
                            </form>
                            <table id="datatablesSimple" border="1">
                            <thead>
                                <tr>
                                    <td rowspan="2">Tanggal</td>
                                    <td rowspan="2">Keterangan</td>
                                    <td colspan="3">Masuk</td>
                                    <td colspan="3">Keluar</td>
                                    <td colspan="3">Persediaan</td>
                                </tr>
                                <tr>
                                    <td>Unit</td>
                                    <td>Harga</td>
                                    <td>Total</td>
                                    <td>Unit</td>
                                    <td>Harga</td>
                                    <td>Total</td>
                                    <td>Unit</td>
                                    <td>Harga</td>
                                    <td>Total</td>
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                                    <td>Modal Awal</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td> <!--  isi dengan jumlah unit jika menggunakan filter nama barang--></td>
                                    <td> <!--  isi dengan harga/unit jika menggunakan filter nama barang--></td>
                                    <td> <!--  isi dengan total bayar --></td>
                                </tr>
                                <tr>
                                    <td><!--  tanggal --></td>
                                    <td><!--  keterangan --></td>
                                    <td><!--  unit --></td>
                                    <td><!--  harga --></td>
                                    <td><!--  total --></td>
                                    <td><!--  unit --></td>
                                    <td><!--  harga --></td>
                                    <td><!--  total --></td>
                                    <td><!--  unit --></td>
                                    <td><!--  harga --></td>
                                    <td><!--  total --></td>
                                </tr>
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


    <script>
        // Menampilkan data modal awal pada tabel
        window.onload = function() {
            var modalAwalData = JSON.parse(localStorage.getItem("modalAwal")) || {};
            var tableBody = document.querySelector("#datatablesSimple tbody");
            var row = tableBody.insertRow();

            row.insertCell().textContent = "Modal Awal";
            row.insertCell().textContent = "";
            row.insertCell().textContent = "";
            row.insertCell().textContent = "";
            row.insertCell().textContent = "";
            row.insertCell().textContent = "";
            row.insertCell().textContent = "";
            row.insertCell().textContent = modalAwalData['<?php echo $kode_barang; ?>'].stok_awal;
            row.insertCell().textContent = modalAwalData['<?php echo $kode_barang; ?>'].harga_awal;
            row.insertCell().textContent = modalAwalData['<?php echo $kode_barang; ?>'].stok_awal * modalAwalData['<?php echo $kode_barang; ?>'].harga_awal;

            // Menampilkan data persediaan dari PHP
            <?php foreach ($persediaan as $kode_barang => $unit) { ?>
                var row = tableBody.insertRow();
                row.insertCell().textContent = "<?php echo date('Y-m-d'); ?>";
                row.insertCell().textContent = "Update Persediaan";
                row.insertCell().textContent = "<?php echo $unit; ?>";
                row.insertCell().textContent = "<?php echo number_format($averagePrice[$kode_barang], 2); ?>";
                row.insertCell().textContent = "<?php echo number_format($averagePrice[$kode_barang] * $unit, 2); ?>";
                row.insertCell().textContent = "";
                row.insertCell().textContent = "";
                row.insertCell().textContent = "<?php echo $unit; ?>";
                row.insertCell().textContent = "<?php echo number_format($averagePrice[$kode_barang], 2); ?>";
                row.insertCell().textContent = "<?php echo number_format($averagePrice[$kode_barang] * $unit, 2); ?>";
            <?php } ?>
        };
    </script>
</body>
</html>

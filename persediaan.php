<?php
require 'function.php';

// Fetching initial data for modal awal
$barangQuery = "SELECT kode_barang, harga_beli, nama_barang FROM barang";
$barangResult = mysqli_query($conn, $barangQuery);


// ambil barang
$jumlahbarangSUM = "SELECT SUM(jumlah_barang) AS total_jumlah_barang FROM barang";
$jumlahbarangResult = mysqli_query($conn, $jumlahbarangSUM);
$totalJumlahBarang = 0;

if ($jumlahbarangResult) {
    $row = mysqli_fetch_assoc($jumlahbarangResult);
    $totalJumlahBarang = $row['total_jumlah_barang'];
} else {
    echo "Error: " . mysqli_error($conn);
}
$modalQuery = "SELECT SUM(jumlah_barang) AS total_jumlah, SUM(harga_beli) AS total_harga_beli, SUM(total_harga) AS total_harga FROM modal_awal";
$modalResult = mysqli_query($conn, $modalQuery);
$modalData = mysqli_fetch_assoc($modalResult);

// Handling form submission to insert initial stock into kartu_persediaan
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

// Fetching data for the table based on kode_barang filter
$kode_barang_filter = isset($_GET['kode_barang']) ? $_GET['kode_barang'] : '';

$tableQuery = "SELECT kp.tanggal_persediaan, kp.kode_det_pembelian, kp.unit_masuk, kp.harga_masuk, kp.total_masuk, kp.unit_keluar, kp.harga_keluar, kp.total_keluar, kp.unit_persediaan, kp.harga_persediaan, kp.total_persediaan
              FROM kartu_persediaan kp
              JOIN detail_pembelian dp ON kp.kode_det_pembelian = dp.kode_det_pembelian";
if (!empty($kode_barang_filter)) {
    $tableQuery .= " WHERE dp.kode_barang = '$kode_barang_filter'";
}
$tableResult = mysqli_query($conn, $tableQuery);
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
                       
                        <div class="card-body">
                        <form method="GET" class="mb-4">
    <div class="form-inline">
        <select class="form-select" name="kode_barang" onchange="this.form.submit()">
            <option value="">Semua</option>
            <?php while ($row = mysqli_fetch_assoc($barangResult)) { ?>
                <?php $selected = ($_GET['kode_barang'] ?? '') == $row['kode_barang'] ? 'selected' : ''; ?>
                <option value="<?php echo $row['kode_barang']; ?>" <?php echo $selected; ?>><?php echo $row['nama_barang']; ?></option>
            <?php } ?>
        </select>
    </div>
</form>

                            <table id="datatablesSimple" border="1">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Unit Masuk</th>
            <th>Harga Masuk</th>
            <th>Total Masuk</th>
            <th>Unit Keluar</th>
            <th>Harga Keluar</th>
            <th>Total Keluar</th>
            <th>Unit Persediaan</th>
            <th>Harga Persediaan</th>
            <th>Total Persediaan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <td><b>Modal Awal</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><?php echo $modalData['total_jumlah']; ?></td>
            <td><?php echo $modalData['total_harga_beli']; ?></td>
            <td><?php echo $modalData['total_harga']; ?></td>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($tableResult)) { ?>
            <tr>
                <td><?php echo $row['tanggal_persediaan']; ?></td>
                <td><?php echo $row['kode_det_pembelian']; ?></td>
                <td><?php echo $row['unit_masuk']; ?></td>
                <td><?php echo $row['harga_masuk']; ?></td>
                <td><?php echo $row['total_masuk']; ?></td>
                <td><?php echo $row['unit_keluar']; ?></td>
                <td><?php echo $row['harga_keluar']; ?></td>
                <td><?php echo $row['total_keluar']; ?></td>
                <td><?php echo $row['unit_persediaan']; ?></td>
                <td><?php echo $row['harga_persediaan']; ?></td>
                <td><?php echo $row['total_persediaan']; ?></td>
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


    <script>
        // Menampilkan data modal awal pada tabel
        
          
    </script>
</body>
</html>

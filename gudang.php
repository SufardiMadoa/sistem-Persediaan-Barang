<?php
require 'function.php'; // Ensure this file includes database connection

// Initialize variables
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Database query to fetch data from kartu_persediaan and barang tables
$sql = "
    SELECT
        kp.tanggal_persediaan AS tanggal_stok,
        b.nama_barang,
        kp.unit_persediaan AS total_persediaan,
        b.jumlah_minimum
    FROM
        (
            SELECT
                MAX(kp.tanggal_persediaan) AS max_tanggal,
                kp.kode_det_pembelian
            FROM
                kartu_persediaan kp
            WHERE
                kp.tanggal_persediaan BETWEEN '$startDate' AND '$endDate'
            GROUP BY
                kp.kode_det_pembelian
        ) AS max_dates
    INNER JOIN kartu_persediaan kp ON kp.kode_det_pembelian = max_dates.kode_det_pembelian AND kp.tanggal_persediaan = max_dates.max_tanggal
    LEFT JOIN barang b ON b.kode_barang = kp.kode_det_pembelian
    ORDER BY
        b.nama_barang ASC, kp.tanggal_persediaan ASC"; // Order by nama_barang and tanggal_persediaan

$result = mysqli_query($conn, $sql);

if (!$result) {
    // If query fails, handle the error
    $error_message = mysqli_error($conn);
    echo "Query error: $error_message";
    exit;
}

$dataStokGudang = [];
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $dataStokGudang[] = [
        'no' => $no++,
        'tanggal_stok' => $row['tanggal_stok'],
        'nama_barang' => $row['nama_barang'],
        'total_persediaan' => $row['total_persediaan'],
        'jumlah_minimum' => $row['jumlah_minimum']
    ];
}

// Close database connection
mysqli_close($conn);
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
                        <a class="nav-link" href="gudang.php">
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
                    <h1 class="mt-4">Kartu Stok Gudang</h1>
                    <div class="card mb-4">
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
                                        <th>No</th>
                                        <th>Tanggal Stok</th>
                                        <th>Nama Barang</th>
                                        <th>Total persediaan</th>
                                        <th>Jumlah Minimum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($dataStokGudang as $row): ?>
                                    <tr>
                                        <td><?= $row['no'] ?></td>
                                        <td><?= $row['tanggal_stok'] ?></td>
                                        <td><?= $row['nama_barang'] ?></td>
                                        <td><?= $row['total_persediaan'] ?></td>
                                        <td><?= $row['jumlah_minimum'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
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
</html>

<?php
require 'function.php';

// Proses tambah pemasok
if (isset($_POST['tambah'])) {
    $id_pemasok = mysqli_real_escape_string($conn, $_POST['id_pemasok']);
    $nama_pemasok = mysqli_real_escape_string($conn, $_POST['nama_pemasok']);
    $alamat_pemasok = mysqli_real_escape_string($conn, $_POST['alamat_pemasok']);
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);

    // Query SQL untuk memasukkan data ke dalam tabel pemasok
    $query = "INSERT INTO pemasok (id_pemasok, nama_pemasok, alamat_pemasok, no_telp) VALUES ('$id_pemasok', '$nama_pemasok', '$alamat_pemasok', '$no_telp')";

    if (mysqli_query($conn, $query)) {
        echo '<script>alert("Data pemasok berhasil ditambahkan.");</script>';
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}

// Ambil data pemasok dari database untuk ditampilkan
$sql = "SELECT * FROM pemasok";
$result = mysqli_query($conn, $sql);
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
                    <h1 class="mt-4">Daftar Pemasok</h1>
                    <div class="card mb-4">
                        <div class="card-header">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">+ Tambah</button>
                        </div>
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>ID Pemasok</th>
                                        <th>Nama Pemasok</th>
                                        <th>Alamat Pemasok</th>
                                        <th>No Telepon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Tampilkan data pemasok dari database
                                    if (mysqli_num_rows($result) > 0) {
                                        while($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>" . $row['id_pemasok'] . "</td>";
                                            echo "<td>" . $row['nama_pemasok'] . "</td>";
                                            echo "<td>" . $row['alamat_pemasok'] . "</td>";
                                            echo "<td>" . $row['no_telp'] . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4'>Tidak ada data pemasok.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <div class="card-body">
                                <!-- Table data -->
                            </div>
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
<!-- Modal untuk input data pemasok -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Input Pemasok</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- Modal body -->
            <form method="post" action="pemasok.php">
                <div class="modal-body" style="display: grid; grid-gap: 10px;">
                    <div>
                        <label for="id_pemasok">ID Pemasok</label>
                        <input type="text" id="id_pemasok" name="id_pemasok" placeholder="ID Pemasok" class="form-control" required>
                    </div>
                    <div>
                        <label for="nama_pemasok">Nama Pemasok</label>
                        <input type="text" id="nama_pemasok" name="nama_pemasok" placeholder="Nama Pemasok" class="form-control" required>
                    </div>
                    <div>
                        <label for="alamat_pemasok">Alamat</label>
                        <input type="text" id="alamat_pemasok" name="alamat_pemasok" placeholder="Alamat" class="form-control" required>
                    </div>
                    <div>
                        <label for="no_telp">No Telepon</label>
                        <input type="text" id="no_telp" name="no_telp" placeholder="No Telepon" class="form-control" required>
                    </div>
                    <div style="text-align: center;">
                        <button type="submit" class="btn btn-primary" name="tambah">Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</html>

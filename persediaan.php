<?php
require 'function.php';

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

// Menghitung harga rata-rata dan persediaan dari data pembelian
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

// Menampilkan tabel persediaan
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- (head content as before) -->
</head>
<body class="sb-nav-fixed">
    <!-- (navigation and sidebar as before) -->
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Laporan Penjualan</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <!-- (header content as before) -->
                    </div>
                    <div class="card-body">
                        <!-- Filter Tanggal -->
                        <form method="GET" class="mb-4">
                            <!-- (form content as before) -->
                        </form>
                        <table id="datatablesSimple" border="1">
                            <thead>
                                <tr>
                                    <td>Kode Barang</td>
                                    <td>Nama Barang</td>
                                    <td>Jumlah Persediaan</td>
                                    <td>Harga Rata-rata</td>
                                    <td>Total Nilai Persediaan</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Mengambil nama barang dari tabel barang
                                $barangQuery = "SELECT kode_barang, nama_barang FROM barang";
                                $barangResult = mysqli_query($conn, $barangQuery);
                                
                                while ($barang = mysqli_fetch_assoc($barangResult)) {
                                    $kode_barang = $barang['kode_barang'];
                                    $nama_barang = $barang['nama_barang'];
                                    $jumlah_persediaan = isset($persediaan[$kode_barang]) ? $persediaan[$kode_barang] : 0;
                                    $harga_rata = isset($averagePrice[$kode_barang]) ? $averagePrice[$kode_barang] : 0;
                                    $total_nilai = $jumlah_persediaan * $harga_rata;

                                    echo "<tr>";
                                    echo "<td>{$kode_barang}</td>";
                                    echo "<td>{$nama_barang}</td>";
                                    echo "<td>{$jumlah_persediaan}</td>";
                                    echo "<td>{$harga_rata}</td>";
                                    echo "<td>{$total_nilai}</td>";
                                    echo "</tr>";
                                }
                                ?>
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
</html>

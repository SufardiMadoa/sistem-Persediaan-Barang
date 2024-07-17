<?php
require 'config.php';  // Memasukkan file koneksi ke database
check_login();
// Fungsi untuk mengecek apakah pengguna sudah login
// function check_login() {
//     if (!isset($_SESSION['log'])) {
//         header('Location: login.php');
//         exit();
//     }
// }

// Fungsi untuk menyimpan data barang ke database
function tambah_barang($kode_barang, $nama_barang, $jumlah_barang, $harga_beli, $harga_jual, $keterangan) {
    global $koneksi;
    
    $kode_barang = mysqli_real_escape_string($koneksi, $kode_barang);
    $nama_barang = mysqli_real_escape_string($koneksi, $nama_barang);
    $jumlah_barang = mysqli_real_escape_string($koneksi, $jumlah_barang);
    $harga_beli = mysqli_real_escape_string($koneksi, $harga_beli);
    $harga_jual = mysqli_real_escape_string($koneksi, $harga_jual);
    $keterangan = mysqli_real_escape_string($koneksi, $keterangan);
    
    $sql = "INSERT INTO daftar_barang (kode_barang, nama_barang, jumlah_barang, harga_beli, harga_jual, keterangan) 
            VALUES ('$kode_barang', '$nama_barang', '$jumlah_barang', '$harga_beli', '$harga_jual', '$keterangan')";
    
    if ($koneksi->query($sql) === TRUE) {
        return true;  // Jika berhasil menyimpan data
    } else {
        return false; // Jika terjadi kesalahan
    }
}


// fungsi untuk menyimpan data pengguna ke database 
function insertData($id_pengguna, $nama_pengguna, $email, $jabatan) {
    $conn = connectDatabase();

    // SQL query untuk insert data
    $sql = "INSERT INTO nama_tabel (id_pengguna, nama_pengguna, email, jabatan)
            VALUES ('$id_pengguna', '$nama_pengguna', '$email', '$jabatan')";

    if ($conn->query($sql) === TRUE) {
        echo "Data berhasil ditambahkan";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Menutup koneksi
    $conn->close();
}

function getStokGudang($startDate, $endDate) {
    global $conn; // pastikan koneksi database sudah diatur

    // Query untuk mendapatkan stok terakhir pada rentang tanggal
    $query = "SELECT b.nama_barang, kp.tanggal_persediaan, SUM(kp.unit_persediaan) AS total_persediaan
              FROM barang b
              JOIN kartu_persediaan kp ON b.kode_barang = kp.kode_det_pembelian OR b.kode_barang = kp.kode_det_penjualan
              WHERE kp.tanggal_persediaan BETWEEN '$startDate' AND '$endDate'
              GROUP BY b.nama_barang, kp.tanggal_persediaan
              ORDER BY kp.tanggal_persediaan DESC";
    
    $result = mysqli_query($conn, $query);

    $rows = [];
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = [
            'no' => $no++,
            'tanggal_stok' => $row['tanggal_persediaan'],
            'nama_barang' => $row['nama_barang'],
            'total_persediaan' => $row['total_persediaan'],
            'jumlah_minimum' => 10, // contoh jumlah minimum
        ];
    }

    return $rows;
}

?>


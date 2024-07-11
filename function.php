<?php
require 'config.php';  // Memasukkan file koneksi ke database

// Fungsi untuk mengecek apakah pengguna sudah login
function check_login() {
    if (!isset($_SESSION['log'])) {
        header('Location: login.php');
        exit();
    }
}

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

?>


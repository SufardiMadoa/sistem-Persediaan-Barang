<?php
require 'function.php';
check_login();

// Menambah Barang Baru
if (isset($_POST['addnewbarang'])) {
    $kode_barang = mysqli_real_escape_string($conn, $_POST['kode_barang']);
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $jumlah_barang = mysqli_real_escape_string($conn, $_POST['jumlah_barang']);
    $harga_beli = mysqli_real_escape_string($conn, $_POST['harga_beli']);
    $harga_jual = mysqli_real_escape_string($conn, $_POST['harga_jual']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $addtotable = mysqli_query($conn, "INSERT INTO barang (kode_barang, nama_barang, jumlah_barang, harga_beli, harga_jual, keterangan) VALUES ('$kode_barang', '$nama_barang', '$jumlah_barang', '$harga_beli', '$harga_jual', '$keterangan')");
    
    if ($addtotable) {
        header('Location: index.php');
        exit();
    } else {
        echo 'Gagal menambah barang: ' . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang</title>
</head>
<body>
    <form method="post">
        <label for="kode_barang">Kode Barang:</label>
        <input type="text" id="kode_barang" name="kode_barang" required><br>
        <label for="nama_barang">Nama Barang:</label>
        <input type="text" id="nama_barang" name="nama_barang" required><br>
        <label for="jumlah_barang">Jumlah Barang:</label>
        <input type="number" id="jumlah_barang" name="jumlah_barang" required><br>
        <label for="harga_beli">Harga Beli:</label>
        <input type="number" id="harga_beli" name="harga_beli" required><br>
        <label for="harga_jual">Harga Jual:</label>
        <input type="number" id="harga_jual" name="harga_jual" required><br>
        <label for="keterangan">Keterangan:</label>
        <input type="text" id="keterangan" name="keterangan"><br>
        <button type="submit" name="addnewbarang">Tambah Barang</button>
    </form>
</body>
</html>

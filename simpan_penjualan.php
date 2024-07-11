<?php
// simpan_penjualan.php

require 'function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tangkap data dari form
    $kode_penjualan = $_POST['kode_pembelian'];
    $tgl_penjualan = $_POST['tgl_pembelian'];
    $total_penjualan = $_POST['id_pemasok'];
    $nama_barang = $_POST['nama_barang'];
    $harga_jual = $_POST['harga_jual'];
    $total_bayar = $_POST['total_bayar'];

    // Query SQL untuk menyimpan data penjualan
    $query_penjualan = "INSERT INTO penjualan (kode_penjualan, tgl_penjualan, total_penjualan) VALUES ('$kode_penjualan', '$tgl_penjualan', '$total_penjualan')";

    if ($conn->query($query_penjualan) === TRUE) {
        $penjualan_id = $conn->insert_id;

        // Loop untuk menyimpan detail penjualan
        for ($i = 0; $i < count($nama_barang); $i++) {
            $nama = $nama_barang[$i];
            $harga = $harga_jual[$i];
            $total = $total_bayar[$i];

            // Query SQL untuk menyimpan detail penjualan
            $query_detail = "INSERT INTO detail_penjualan (penjualan_id, nama_barang, harga_jual, total_bayar) VALUES ($penjualan_id, '$nama', $harga, '$total')";
            $conn->query($query_detail);
        }

        // Redirect kembali ke halaman penjualan.php setelah berhasil disimpan
        header("Location: penjualan.php");
        exit();
    } else {
        echo "Error: " . $query_penjualan . "<br>" . $conn->error;
    }
}

// Tutup koneksi
$conn->close();
?>

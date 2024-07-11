<?php
// File ini digunakan untuk memverifikasi password secara langsung untuk tujuan debugging

$plain_password = 'tps12345'; // Password plain text yang ingin diperiksa
$hashed_password = '$2y$10$I'; // Hash password dari database

if (password_verify($plain_password, $hashed_password)) {
    echo "Password is valid!";
} else {
    echo "Invalid password.";
}
?>

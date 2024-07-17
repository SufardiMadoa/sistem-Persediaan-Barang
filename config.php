<?php
session_start();

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'persediaanbarang';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Function to check if the user is logged in
if (!function_exists('check_login')) {
    function check_login() {
        if (!isset($_SESSION['id_pengguna'])) {
            header("Location: login.php");
            exit;
        }
    }
}
?>

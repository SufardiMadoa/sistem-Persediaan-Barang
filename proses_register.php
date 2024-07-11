<?php
// Pastikan method POST digunakan untuk mengambil data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil nilai dari form
    $id_pengguna = $_POST['id_pengguna'];
    $nama_pengguna = $_POST['nama_pengguna'];
    $email = $_POST['email'];
    $jabatan = $_POST['jabatan'];
    $password = $_POST['password'];

    // Hash password sebelum disimpan ke database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Koneksi ke database (sesuaikan dengan informasi koneksi Anda)
    $host = 'localhost';
    $db_username = 'root';
    $db_password = '';
    $persediaanbarang = 'persediaanbarang';

    // Membuat koneksi baru menggunakan mysqli
    $conn = new mysqli($host, $db_username, $db_password, $persediaanbarang);

    // Memeriksa koneksi
    if ($conn->connect_error) {
        die("Koneksi database gagal: " . $conn->connect_error);
    }

    // Menyiapkan query SQL dengan prepared statement
    $sql = "INSERT INTO pengguna (id_pengguna, nama_pengguna, email, jabatan, password) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    // Bind parameter ke statement
    $stmt->bind_param("sssss", $id_pengguna, $nama_pengguna, $email, $jabatan, $hashed_password);

    // Menjalankan statement
    if ($stmt->execute()) {
        echo "Registrasi berhasil! <a href='login.php'>Kembali ke Halaman Login</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Menutup statement dan koneksi
    $stmt->close();
    $conn->close();
}
?>

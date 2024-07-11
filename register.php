<?php
require 'config.php';

if (isset($_POST['register'])) {
    $id_pengguna = mysqli_real_escape_string($conn, $_POST['id_pengguna']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Simpan $hashed_password ke database
    $query = "INSERT INTO pengguna (id_pengguna, password) VALUES ('$id_pengguna', '$hashed_password')";
    if (mysqli_query($conn, $query)) {
        echo "Registrasi berhasil!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            background-color: black;
            color: black; 
            font-family: Arial, sans-serif;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh; /* Membuat form berada di tengah vertikal layar */
        }
        form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
            margin-bottom: 20px; /* Memberi jarak antara form dan paragraf */
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            color: black; 
        }
        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background-color: #45a049;
        }
        .login-link {
            text-align: center;
            color: black; /* warna teks pada paragraf */
            margin-top: 10px;
        }
        .login-link a {
            color: blue;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <form method="post" action="proses_register.php">
        <label for="id_pengguna">ID Pengguna:</label>
        <input type="text" id="id_pengguna" name="id_pengguna" required><br>
        <label for="nama_pengguna">Nama Pengguna:</label>
        <input type="text" id="nama_pengguna" name="nama_pengguna" required><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="jabatan">Jabatan:</label>
        <input type="text" id="jabatan" name="jabatan" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit" name="register">Register</button>
    <div class="login-link">
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
    </form>
</body>
</html>

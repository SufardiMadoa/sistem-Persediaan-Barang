<?php
session_start();
require 'config.php';

$message = '';

if (isset($_POST['login'])) {
    $id_pengguna = mysqli_real_escape_string($conn, $_POST['id_pengguna']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (!empty($id_pengguna) && !empty($password)) {
        $query = "SELECT * FROM pengguna WHERE id_pengguna='$id_pengguna'";
        $result = mysqli_query($conn, $query);

        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                // Memeriksa password dengan password_verify
                if (password_verify($password, $user['password'])) {
                    $_SESSION['log'] = 'true';  // Set session login
                    header('Location: index.php');  // Redirect ke halaman setelah login sukses
                    exit();
                } else {
                    $message = 'Password salah. Silakan coba lagi.';
                }
            } else {
                $message = 'Akun Anda belum terdaftar.';
            }
        } else {
            $message = 'Error dalam melakukan query. Silakan coba lagi.';
            echo mysqli_error($conn);  // Tampilkan pesan error MySQL untuk debugging
        }
    } else {
        $message = 'ID Pengguna dan Password harus diisi.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #000;
            margin: 0;
        }
        .login-container {
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
            margin-top: auto; /* Tambahkan ini untuk mendorong form ke tengah layar */
            margin-bottom: auto; /* Tambahkan ini untuk mendorong form ke tengah layar */
        }
        .login-container h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .login-container h2 {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-container button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>WELCOME!</h1>
        <h2>'UD GUSNIAR KAYU'</h2>
        <form method="post">
            <label for="id_pengguna">ID Pengguna:</label>
            <input type="text" id="id_pengguna" name="id_pengguna" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <button type="submit" name="login">Login</button>
            <?php if (!empty($message)): ?>
                <p class="error-message"><?php echo $message; ?></p>
            <?php endif; ?>
        </form>
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
</body>
</html>

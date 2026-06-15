<?php
session_start();

// Jika user sudah memiliki session (sudah login), langsung arahkan ke timeline
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

// Panggil Class User
require_once 'classes/User.php';
$userObj = new User();

// Memisahkan pesan agar tidak tertukar antara form login dan register
$login_error = "";
$register_error = "";
$register_success = "";

// Variabel untuk melacak form mana yang terakhir dibuka
$show_register = false;

// 1. Menangkap Method POST dari Form Registrasi
if (isset($_POST['register'])) {
    $show_register = true; // Tetap buka form register setelah disubmit
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($userObj->register($username, $password, $name)) {
        $register_success = "Pendaftaran berhasil! Silakan Masuk, Meow!";
        $show_register = false; // Jika sukses, arahkan kembali ke form Login
    } else {
        $register_error = "Meow-af, Username sudah digunakan!";
    }
}

// 2. Menangkap Method POST dari Form Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($userObj->login($username, $password)) {
        header("Location: home.php");
        exit;
    } else {
        $login_error = "Meow-af, Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Meower</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        h1 {
            color: #ff914d;
            margin-bottom: 5px;
        }

        p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }

        input {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #ff914d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background-color: #e57c38;
        }

        .toggle-link {
            color: #ff914d;
            cursor: pointer;
            text-decoration: underline;
            font-size: 13px;
            margin-top: 20px;
            display: inline-block;
        }

        .msg {
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .error {
            background-color: #ffe6e6;
            color: #d9534f;
            border: 1px solid #d9534f;
        }

        .success {
            background-color: #e6ffe6;
            color: #5cb85c;
            border: 1px solid #5cb85c;
        }
    </style>
</head>

<body>

    <div class="container" id="login-box" style="display: <?php echo $show_register ? 'none' : 'block'; ?>;">
        <h1>🐱 Meower</h1>
        <p>Bagikan "Meow" mu ke seluruh dunia!</p>

        <?php if ($login_error) echo "<div class='msg error'>$login_error</div>"; ?>
        <?php if ($register_success) echo "<div class='msg success'>$register_success</div>"; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Masuk</button>
        </form>
        <span class="toggle-link" onclick="toggleForms()">Belum punya akun? Daftar di sini!</span>
    </div>

    <div class="container" id="register-box" style="display: <?php echo $show_register ? 'block' : 'none'; ?>;">
        <h1>🐱 Daftar Meower</h1>
        <p>Gabung bersama para kucing lainnya!</p>

        <?php if ($register_error) echo "<div class='msg error'>$register_error</div>"; ?>

        <form method="POST" action="">
            <input type="text" name="name" placeholder="Display Name" required>
            <input type="text" name="username" placeholder="Username (Tanpa Spasi)" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Daftar</button>
        </form>
        <span class="toggle-link" onclick="toggleForms()">Sudah punya akun? Masuk di sini!</span>
    </div>

    <script>
        function toggleForms() {
            var loginBox = document.getElementById('login-box');
            var registerBox = document.getElementById('register-box');
            if (loginBox.style.display === 'none') {
                loginBox.style.display = 'block';
                registerBox.style.display = 'none';
            } else {
                loginBox.style.display = 'none';
                registerBox.style.display = 'block';
            }
        }
    </script>

</body>

</html>
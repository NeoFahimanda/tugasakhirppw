<?php
session_start();

// Jika user sudah memiliki session (sudah login), langsung arahkan ke timeline
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

// Di bagian atas file login.php, ambil data dari cookie jika ada
$saved_username = isset($_COOKIE['meower_user']) ? $_COOKIE['meower_user'] : '';

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
        // Jika checkbox dicentang, buat cookie selama 30 hari
        if (isset($_POST['remember'])) {
            setcookie('meower_user', $username, time() + (86400 * 30), "/");
        }
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
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="login-body <?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark' ? 'dark-mode' : ''; ?>">

    <div class="login-container" id="login-box" style="display: <?php echo $show_register ? 'none' : 'block'; ?>;">
        <h1>🐱 Meower</h1>
        <p>Bagikan "Meow" mu ke seluruh dunia!</p>
        
        <?php if ($login_error) echo "<div class='msg error'>$login_error</div>"; ?>
        <?php if ($register_success) echo "<div class='msg success'>$register_success</div>"; ?>
        
        <form method="POST" action="">
            <input type="text" name="username" value="<?php echo htmlspecialchars($saved_username); ?>" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <div style="margin: 15px 0; text-align: left; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="remember" id="remember" style="width: auto; margin: 0;">
                <label for="remember" style="color: var(--text-muted); cursor: pointer;">Ingat Saya</label>
            </div>
            <button type="submit" name="login">Masuk</button>
        </form>
        <span class="toggle-link" onclick="toggleForms()">Belum punya akun? Daftar di sini!</span>
    </div>

    <div class="login-container" id="register-box" style="display: <?php echo $show_register ? 'block' : 'none'; ?>;">
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

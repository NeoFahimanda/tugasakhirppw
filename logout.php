<?php
session_start();

// Menghapus semua variabel session
session_unset();

// Menghancurkan session
session_destroy();

// Arahkan kembali ke halaman awal (Landing Page)
header("Location: home.php");
exit;

<?php
session_start();

// Proteksi Download Berkas: Pengunjung Wajib Login! (Sesuai Syaratmu)
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Meow-af, silakan login terlebih dahulu untuk mengunduh gambar!'); window.location.href='login.php';</script>";
    exit;
}

// Cek apakah ada parameter file di URL GET
if (isset($_GET['file']) && !empty($_GET['file'])) {
    $filename = basename($_GET['file']); // Menggunakan basename demi keamanan path traversal
    $filepath = "uploads/posts/" . $filename;

    // Cek apakah file fisiknya benar-benar ada di folder server
    if (file_exists($filepath)) {

        // Memanipulasi Header HTTP untuk Memaksa Browser Melakukan Download File (Materi Praktikum 13-14)
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));

        // Bersihkan output buffer server agar file tidak korup/rusak saat diunduh
        flush();

        // Baca berkas dan kirimkan ke browser klien
        readfile($filepath);
        exit;
    } else {
        echo "<h2>Meow-af, File tidak ditemukan di server!</h2><a href='home.php'>Kembali</a>";
    }
} else {
    header("Location: home.php");
    exit;
}

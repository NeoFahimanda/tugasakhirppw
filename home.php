<?php
session_start();
// Ambil data cookie tema, jika belum disetel default-nya adalah 'light'
$theme_preference = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// ... sisa require_once dan logika database yang sudah ada ...
// ... kode yang sudah ada ...
require_once 'classes/Post.php';
require_once 'classes/Interaction.php'; // Tambahkan ini

$postObj = new Post();
$interactionObj = new Interaction(); // Inisialisasi Class baru

// Jika form "Meow" disubmit (Menggunakan POST)
if (isset($_POST['submit_post']) && isset($_SESSION['user_id'])) {
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    $post_image = null;

    // Logika Upload Gambar Postingan (Materi Praktikum 13-14)
    if (!empty($_FILES['post_img']['name'])) {
        $image_name = time() . "_" . $_FILES['post_img']['name'];
        $image_tmp = $_FILES['post_img']['tmp_name'];
        $target_path = "uploads/posts/" . $image_name;

        if (move_uploaded_file($image_tmp, $target_path)) {
            $post_image = $image_name;
        }
    }

    if ($postObj->createPost($user_id, $content, $post_image)) {
        header("Location: home.php");
        exit;
    }
}

// ... [Kode submit_post yang sudah ada] ...

// Jika ada permintaan Hapus Meow (Metode GET)
if (isset($_GET['delete_id']) && isset($_SESSION['user_id'])) {
    $post_id = $_GET['delete_id'];
    $user_id = $_SESSION['user_id'];

    $postObj->deletePost($post_id, $user_id);
    header("Location: home.php"); // Refresh setelah dihapus
    exit;
}

if (isset($_GET['like_id'])) {
    if (!isset($_SESSION['user_id'])) {
        // Jika visitor mencoba like
        echo "<script>alert('Meow-af, silakan login untuk menyukai Meow ini!'); window.location.href='login.php';</script>";
        exit;
    }

    // Jika user sudah login, jalankan toggle like
    $interactionObj->toggleLike($_SESSION['user_id'], $_GET['like_id']);
    header("Location: home.php");
    exit;
}
// =====================================

// === LOGIKA FILTER TIMELINE / SEARCH ===
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $keyword = $_GET['search'];
    // Jika ada parameter search, panggil fungsi pencarian LIKE MySQL
    $all_posts = $postObj->searchPosts($keyword);
} else {
    // Jika tidak ada pencarian, tampilkan semua postingan seperti biasa
    $all_posts = $postObj->getAllPosts();
}
// =======================================

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Home / Meower</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
        }

        .layout-container {
            display: flex;
            max-width: 1000px;
            margin: 0 auto;
            min-height: 100vh;
        }

        /* Kolom Kiri */
        .left-col {
            width: 25%;
            padding: 20px;
            border-right: 1px solid #ddd;
            background: white;
        }

        .left-col a {
            display: block;
            padding: 10px 0;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            font-size: 18px;
        }

        .left-col a:hover {
            color: #ff914d;
        }

        /* Kolom Tengah */
        .mid-col {
            width: 50%;
            background: white;
        }

        .header {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 20px;
            position: sticky;
            top: 0;
            background: rgba(255, 255, 255, 0.9);
        }

        .post-form {
            padding: 20px;
            border-bottom: 10px solid #f0f2f5;
        }

        .post-form textarea {
            width: 100%;
            border: none;
            outline: none;
            font-size: 18px;
            resize: none;
        }

        .btn-upload {
            margin-top: 10px;
            font-size: 14px;
        }

        .btn-meow {
            background: #ff914d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            float: right;
            margin-top: 10px;
        }

        /* Tampilan Feed Postingan */
        .feed-post {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            gap: 15px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #ddd;
            object-fit: cover;
        }

        .post-content h4 {
            margin: 0 0 5px 0;
            display: inline-block;
        }

        .post-content span {
            color: #888;
            font-size: 14px;
        }

        .post-content p {
            margin: 10px 0 0 0;
            line-height: 1.5;
        }

        /* Kolom Kanan */
        .right-col {
            width: 25%;
            padding: 20px;
            border-left: 1px solid #ddd;
            background: white;
        }

        .flash-msg {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Sembunyikan input file bawaan browser yang kaku */
        .input-file-hidden {
            display: none;
        }

        /* Buat tombol kustom dengan ikon/emoji */
        .custom-file-upload {
            display: inline-block;
            padding: 8px 15px;
            cursor: pointer;
            background: #fff0ea;
            color: #ff914d;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
            border: 1px dashed #ff914d;
            transition: background 0.2s;
        }

        .custom-file-upload:hover {
            background: #ffe3dc;
        }

        /* Wadah penampung preview gambar sebelum di-post */
        .preview-container {
            margin-top: 15px;
            position: relative;
            display: none;
            /* Sembunyikan jika belum ada gambar dipilih */
        }

        .preview-container img {
            max-width: 100%;
            max-height: 250px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        /* Tombol X untuk membatalkan pilihan gambar */
        .btn-remove-preview {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
            line-height: 25px;
            text-align: center;
            padding: 0;
        }

        /* === CSS DARK MODE STYLING === */
        body.dark-mode {
            background-color: #15202b;
            color: #ffffff;
        }

        body.dark-mode .mid-col,
        body.dark-mode .left-col,
        body.dark-mode .right-col {
            background-color: #15202b;
            border-color: #38444d;
            color: #ffffff;
        }

        body.dark-mode .header {
            background: rgba(21, 32, 43, 0.9);
            border-color: #38444d;
            color: #ffffff;
        }

        body.dark-mode .feed-post {
            border-color: #38444d;
        }

        body.dark-mode .feed-post h4,
        body.dark-mode .left-col a {
            color: #ffffff;
        }

        body.dark-mode .post-form {
            padding: 20px;
            border-bottom: 10px solid #192734;
        }

        body.dark-mode .post-form textarea {
            background-color: transparent;
            color: #ffffff;
        }

        body.dark-mode input[name="search"] {
            background-color: #253341 !important;
            border-color: #38444d !important;
            color: #ffffff !important;
        }

        body.dark-mode .custom-file-upload {
            background: #1e2d3b;
        }

        /* Mode Terang */
        .widget-box {
            background-color: #f7f9fa;
            color: #000;
        }

        /* Mode Gelap */
        body.dark-mode .widget-box {
            background-color: #192734;
            color: #fff;
        }
    </style>
</head>

<body class="<?php echo $theme_preference == 'dark' ? 'dark-mode' : ''; ?>">

    <div class="layout-container">

        <div class="left-col">
            <?php include 'components/sidebar.php'; ?>
        </div>

        <div class="mid-col">
            <div class="header">Home</div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="post-form">
                    <form method="POST" action="" enctype="multipart/form-data" id="meowForm">
                        <textarea name="content" rows="3" placeholder="Apa yang sedang kamu pikirkan, Meow?" required></textarea>

                        <div class="preview-container" id="previewWrapper">
                            <button type="button" class="btn-remove-preview" id="cancelPreview">✕</button>
                            <img src="" id="imagePreview" alt="Preview Gambar">
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label for="post_img" class="custom-file-upload">
                                Tambah Gambar
                            </label>
                            <input type="file" name="post_img" id="post_img" accept="image/png, image/jpeg" class="input-file-hidden">

                            <button type="submit" name="submit_post" class="btn-meow">Meow</button>
                        </div>
                    </form>
                </div>

                <script>
                    const fileInput = document.getElementById('post_img');
                    const previewWrapper = document.getElementById('previewWrapper');
                    const imagePreview = document.getElementById('imagePreview');
                    const cancelPreview = document.getElementById('cancelPreview');

                    // Mendengarkan perubahan jika user memilih file gambar
                    fileInput.addEventListener('change', function() {
                        const file = this.files[0];

                        if (file) {
                            const reader = new FileReader();

                            // Saat file selesai dibaca oleh browser, tampilkan ke tag img preview
                            reader.addEventListener('load', function() {
                                imagePreview.setAttribute('src', this.result);
                                previewWrapper.style.display = 'block'; // Tampilkan wadah preview
                            });

                            reader.readAsDataURL(file);
                        }
                    });

                    // Jika tombol ✕ ditekan, batalkan pilihan gambar
                    cancelPreview.addEventListener('click', function() {
                        fileInput.value = ""; // Kosongkan file input asli
                        imagePreview.setAttribute('src', '');
                        previewWrapper.style.display = 'none'; // Sembunyikan kembali wadah preview
                    });
                </script>
            <?php else: ?>
                <div class="post-form" style="text-align:center; color:#888;">
                    <p>Silakan login untuk membagikan Meow-mu.</p>
                </div>
            <?php endif; ?>



            <?php foreach ($all_posts as $post): ?>
                <div class="feed-post" id="post-<?php echo $post['id']; ?>">
                    <a href="profile.php?username=<?php echo urlencode($post['username']); ?>">
                        <img src="uploads/avatars/<?php echo $post['profile_pic']; ?>" class="avatar" alt="ava" onerror="this.src='https://via.placeholder.com/50'">
                    </a>
                    <div class="post-content" style="width: 100%;">

                        <div>
                            <a href="profile.php?username=<?php echo urlencode($post['username']); ?>" style="text-decoration: none; color: inherit;">
                                <h4 style="margin: 0; display: inline-block; cursor: pointer;"><?php echo htmlspecialchars($post['name']); ?></h4>
                                <span style="color: #888; font-size: 14px; cursor: pointer;"> @<?php echo htmlspecialchars($post['username']); ?></span>
                            </a>

                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                <a href="home.php?delete_id=<?php echo $post['id']; ?>" onclick="return confirm('Yakin ingin menghapus Meow ini?')" style="color: red; text-decoration: none; font-size: 12px; float: right;">🗑️ Hapus</a>
                            <?php endif; ?>
                        </div>

                        <a href="post_detail.php?id=<?php echo $post['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                            <p style="margin: 10px 0 0 0; line-height: 1.5; cursor: pointer;">
                                <?php echo htmlspecialchars($post['content']); ?>
                            </p>
                            <?php if (!empty($post['post_image'])): ?>
                                <div style="margin-top: 10px;">
                                    <img src="uploads/posts/<?php echo $post['post_image']; ?>" style="max-width: 100%; max-height: 300px; border-radius: 8px; object-fit: cover; border: 1px solid #eee;">

                                    <div style="margin-top: 5px;">
                                        <a href="download.php?file=<?php echo urlencode($post['post_image']); ?>" style="text-decoration: none; font-size: 12px; color: #ff914d; font-weight: bold;">Download Gambar</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </a>

                        <?php
                        $like_count = $interactionObj->getLikeCount($post['id']);
                        $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                        $is_liked = $interactionObj->isLikedByUser($current_user_id, $post['id']);
                        ?>

                        <div style="margin-top: 15px; display: flex; gap: 20px;">
                            <a href="home.php?like_id=<?php echo $post['id']; ?>#post-<?php echo $post['id']; ?>" style="text-decoration: none; font-size: 14px; color: #555;">
                                <?php echo $is_liked ? '❤️' : '🤍'; ?>
                                <span style="<?php echo $is_liked ? 'color: red; font-weight: bold;' : ''; ?>">
                                    <?php echo $like_count; ?>
                                </span>
                            </a>

                            <a href="post_detail.php?id=<?php echo $post['id']; ?>" style="text-decoration: none; font-size: 14px; color: #555;">
                                💬 Balas
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

        <div class="right-col">
            <?php include 'components/widget.php'; ?>
        </div>

    </div>

</body>

</html>
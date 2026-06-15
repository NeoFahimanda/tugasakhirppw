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
    $is_ghost = isset($_POST['is_ghost']) ? 1 : 0;

    // Logika Upload Gambar Postingan (Materi Praktikum 13-14)
    if (!empty($_FILES['post_img']['name'])) {
        $image_name = time() . "_" . $_FILES['post_img']['name'];
        $image_tmp = $_FILES['post_img']['tmp_name'];
        $target_path = "uploads/posts/" . $image_name;

        if (move_uploaded_file($image_tmp, $target_path)) {
            $post_image = $image_name;
        }
    }

    if ($postObj->createPost($user_id, $content, $post_image, $is_ghost)) {
        header("Location: home.php");
        exit;
    }
}

// Jika ada permintaan Hapus Meow (Metode GET)
if (isset($_GET['delete_id']) && isset($_SESSION['user_id'])) {
    $post_id = $_GET['delete_id'];
    $user_id = $_SESSION['user_id'];

    $postObj->deletePost($post_id, $user_id);
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'home.php';
    header("Location: " . $redirect);
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
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'home.php';
    header("Location: " . $redirect);
    exit;
}

if (isset($_GET['save_id'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Meow-af, silakan login untuk menyimpan Meow ini!'); window.location.href='login.php';</script>";
        exit;
    }
    $interactionObj->toggleSave($_SESSION['user_id'], $_GET['save_id']);
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'home.php';
    header("Location: " . $redirect);
    exit;
}
// =====================================

// === LOGIKA FILTER TIMELINE / SEARCH ===
if (isset($_GET['feed']) && $_GET['feed'] == 'ghost') {
    $all_posts = $postObj->getGhostPosts();
} else if (isset($_GET['search']) && !empty($_GET['search'])) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home / Meower</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="<?php echo $theme_preference == 'dark' ? 'dark-mode' : ''; ?>">

    <div class="layout-container">

        <div class="left-col">
            <?php include 'components/sidebar.php'; ?>
        </div>

        <div class="mid-col">
            <div class="header">
                <?php
                if (isset($_GET['feed']) && $_GET['feed'] == 'ghost') {
                    echo "Ghost Posts 👻";
                } else if (isset($_GET['search']) && !empty($_GET['search'])) {
                    echo "Search Results: " . htmlspecialchars($_GET['search']);
                } else {
                    echo "Home";
                }
                ?>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="post-form">
                    <form method="POST" action="" enctype="multipart/form-data" id="meowForm">
                        <textarea name="content" rows="3" placeholder="Apa yang sedang kamu pikirkan, Meow?" required></textarea>

                        <div class="preview-container" id="previewWrapper">
                            <button type="button" class="btn-remove-preview" id="cancelPreview">✕</button>
                            <img src="" id="imagePreview" alt="Preview Gambar">
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <label for="post_img" class="custom-file-upload">
                                    🖼️ Tambah Gambar
                                </label>
                                <input type="file" name="post_img" id="post_img" accept="image/png, image/jpeg" class="input-file-hidden">

                                <label style="display: flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 700; color: var(--text-muted); cursor: pointer; user-select: none;">
                                    <input type="checkbox" name="is_ghost" value="1" style="width: 16px; height: 16px; accent-color: var(--primary);">
                                    Post as Ghost 👻
                                </label>
                            </div>

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
                    <?php if (isset($post['is_ghost']) && $post['is_ghost'] == 1): ?>
                        <div class="avatar" style="display: flex; align-items: center; justify-content: center; background: var(--border-color); color: var(--text-muted); font-size: 22px;">
                            <i class="bi bi-ghost"></i>
                        </div>
                    <?php else: ?>
                        <a href="profile.php?username=<?php echo urlencode($post['username']); ?>">
                            <img src="uploads/avatars/<?php echo $post['profile_pic']; ?>" class="avatar" alt="ava" onerror="this.src='uploads/avatars/default_avatar.png'">
                        </a>
                    <?php endif; ?>
                    <div class="post-content" style="width: 100%;">

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <?php if (isset($post['is_ghost']) && $post['is_ghost'] == 1): ?>
                                <div>
                                    <h4 style="margin: 0; display: inline-block;">Ghost 👻</h4>
                                    <span style="color: var(--text-muted); font-size: 14px;"> @ghost</span>
                                </div>
                            <?php else: ?>
                                <a href="profile.php?username=<?php echo urlencode($post['username']); ?>" style="text-decoration: none; color: inherit;">
                                    <h4 style="margin: 0; display: inline-block; cursor: pointer;"><?php echo htmlspecialchars($post['name']); ?></h4>
                                    <span style="color: var(--text-muted); font-size: 14px; cursor: pointer;"> @<?php echo htmlspecialchars($post['username']); ?></span>
                                </a>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                <a href="home.php?delete_id=<?php echo $post['id']; ?>" onclick="return confirm('Yakin ingin menghapus Meow ini?')" style="color: #ef4444; text-decoration: none; font-size: 12px; font-weight: 600;">🗑️ Hapus</a>
                            <?php endif; ?>
                        </div>

                        <a href="post_detail.php?id=<?php echo $post['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                            <p style="cursor: pointer;">
                                <?php echo htmlspecialchars($post['content']); ?>
                            </p>
                            <?php if (!empty($post['post_image'])): ?>
                                <div class="post-image-wrapper">
                                    <img src="uploads/posts/<?php echo $post['post_image']; ?>">
                                </div>
                                <div style="margin-top: 8px;">
                                    <a href="download.php?file=<?php echo urlencode($post['post_image']); ?>" class="download-link">📥 Download Gambar</a>
                                </div>
                            <?php endif; ?>
                        </a>

                        <?php
                        $like_count = $interactionObj->getLikeCount($post['id']);
                        $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                        $is_liked = $interactionObj->isLikedByUser($current_user_id, $post['id']);
                        $is_saved = $interactionObj->isSavedByUser($current_user_id, $post['id']);
                        ?>

                        <div class="post-actions">
                            <a href="home.php?like_id=<?php echo $post['id']; ?>#post-<?php echo $post['id']; ?>" class="post-action-btn <?php echo $is_liked ? 'liked' : ''; ?>">
                                <?php echo $is_liked ? '❤️' : '🤍'; ?>
                                <span><?php echo $like_count; ?></span>
                            </a>

                            <a href="post_detail.php?id=<?php echo $post['id']; ?>" class="post-action-btn">
                                💬 <span>Balas</span>
                            </a>

                            <a href="home.php?save_id=<?php echo $post['id']; ?>#post-<?php echo $post['id']; ?>" class="post-action-btn <?php echo $is_saved ? 'saved' : ''; ?>" style="color: <?php echo $is_saved ? 'var(--primary)' : 'inherit'; ?>;">
                                <?php echo $is_saved ? '🔖' : '📑'; ?> <span><?php echo $is_saved ? 'Tersimpan' : 'Simpan'; ?></span>
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

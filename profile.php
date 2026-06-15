<?php
session_start();
// Ambil data cookie tema, jika belum disetel default-nya adalah 'light'
$theme_preference = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// ... sisa require_once dan logika database yang sudah ada ...
require_once 'classes/User.php';
require_once 'classes/Post.php';
require_once 'classes/Interaction.php'; // Tambahkan ini

$userObj = new User();
$postObj = new Post();
$interactionObj = new Interaction(); // Inisialisasi

// Cek apakah ada parameter username di URL (Metode GET)
if (!isset($_GET['username']) || empty($_GET['username'])) {
    header("Location: home.php");
    exit;
}

$username = $_GET['username'];

// === TAMBAHKAN BLOK LOGIKA HAPUS INI ===
if (isset($_GET['delete_id']) && isset($_SESSION['user_id'])) {
    $post_id = $_GET['delete_id'];
    $user_id = $_SESSION['user_id'];

    // Eksekusi fungsi hapus dari objek post
    $postObj->deletePost($post_id, $user_id);

    // Refresh dan kembalikan ke profil user yang sama agar tidak error
    header("Location: profile.php?username=" . urlencode($username));
    exit;
}

if (isset($_GET['like_id'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Meow-af, silakan login untuk menyukai Meow ini!'); window.location.href='login.php';</script>";
        exit;
    }

    $interactionObj->toggleLike($_SESSION['user_id'], $_GET['like_id']);
    header("Location: profile.php?username=" . urlencode($username)); // Kembali ke halaman profil
    exit;
}

if (isset($_GET['save_id'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Meow-af, silakan login untuk menyimpan Meow ini!'); window.location.href='login.php';</script>";
        exit;
    }

    $interactionObj->toggleSave($_SESSION['user_id'], $_GET['save_id']);
    header("Location: profile.php?username=" . urlencode($username));
    exit;
}
// ======================================

$profile_data = $userObj->getUserByUsername($username);

// Jika username tidak ditemukan di database
if (!$profile_data) {
    echo "<h2>Meow-af, Akun tidak ditemukan!</h2><a href='home.php'>Kembali</a>";
    exit;
}

// Ambil postingan khusus user ini
$user_posts = $postObj->getPostsByUserId($profile_data['id']);
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($profile_data['name']); ?> (@<?php echo htmlspecialchars($profile_data['username']); ?>) / Meower</title>
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
            <div class="header-title">
                <a href="home.php" style="margin-right:15px;">⬅️</a>
                <?php echo htmlspecialchars($profile_data['name']); ?>
            </div>

            <div class="profile-banner" style="background-image: url('uploads/headers/<?php echo $profile_data['header_pic']; ?>');"></div>
            <div class="profile-info">
                <img src="uploads/avatars/<?php echo $profile_data['profile_pic']; ?>" class="profile-avatar" alt="Avatar" onerror="this.src='uploads/avatars/default_avatar.png'">

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profile_data['id']): ?>
                    <a href="edit_profile.php" class="btn-edit-profile">✏️ Edit Profile</a>
                <?php endif; ?>

                <div class="profile-text">
                    <h2><?php echo htmlspecialchars($profile_data['name']); ?></h2>
                    <span>@<?php echo htmlspecialchars($profile_data['username']); ?></span>
                    <p><?php echo htmlspecialchars($profile_data['bio'] ?? 'Belum ada bio, Meow...'); ?></p>
                </div>
            </div>

            <?php if (count($user_posts) > 0): ?>
                <?php foreach ($user_posts as $post): ?>
                    <div class="feed-post" id="post-<?php echo $post['id']; ?>">
                        <a href="profile.php?username=<?php echo urlencode($post['username']); ?>">
                            <img src="uploads/avatars/<?php echo $post['profile_pic']; ?>" class="avatar" onerror="this.src='uploads/avatars/default_avatar.png'">
                        </a>
                        <div class="post-content" style="width: 100%;">

                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <a href="profile.php?username=<?php echo urlencode($post['username']); ?>" style="text-decoration: none; color: inherit;">
                                    <h4 style="margin: 0; display: inline-block;"><?php echo htmlspecialchars($post['name']); ?></h4>
                                    <span style="color: var(--text-muted); font-size: 14px;">@<?php echo htmlspecialchars($post['username']); ?></span>
                                </a>

                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                    <a href="profile.php?username=<?php echo urlencode($username); ?>&delete_id=<?php echo $post['id']; ?>" onclick="return confirm('Yakin ingin menghapus Meow ini?')" style="color: #ef4444; text-decoration: none; font-size: 12px; font-weight: 600;">🗑️ Hapus</a>
                                <?php endif; ?>
                            </div>

                            <a href="post_detail.php?id=<?php echo $post['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                                <p><?php echo htmlspecialchars($post['content']); ?></p>
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
                                <a href="profile.php?username=<?php echo urlencode($username); ?>&like_id=<?php echo $post['id']; ?>#post-<?php echo $post['id']; ?>" class="post-action-btn <?php echo $is_liked ? 'liked' : ''; ?>">
                                    <?php echo $is_liked ? '❤️' : '🤍'; ?>
                                    <span><?php echo $like_count; ?></span>
                                </a>
                                <a href="post_detail.php?id=<?php echo $post['id']; ?>" class="post-action-btn">
                                    💬 <span>Balas</span>
                                </a>
                                <a href="profile.php?username=<?php echo urlencode($username); ?>&save_id=<?php echo $post['id']; ?>#post-<?php echo $post['id']; ?>" class="post-action-btn <?php echo $is_saved ? 'saved' : ''; ?>" style="color: <?php echo $is_saved ? 'var(--primary)' : 'inherit'; ?>;">
                                    <?php echo $is_saved ? '🔖' : '📑'; ?> <span><?php echo $is_saved ? 'Tersimpan' : 'Simpan'; ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding:40px 20px; text-align:center; color:#888;">
                    User ini belum pernah menge-Meow.
                </div>
            <?php endif; ?>

        </div>

        <div class="right-col">
            <?php include 'components/widget.php'; ?>
        </div>

    </div>

</body>

</html>

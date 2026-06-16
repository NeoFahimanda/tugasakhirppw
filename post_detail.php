<?php
session_start();
// Membaca cookie preferensi tema (Default: light)
$theme_preference = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

require_once 'classes/User.php';
require_once 'classes/Post.php';
require_once 'classes/Interaction.php';
require_once 'classes/Flash.php'; // Pastikan class Flash di-include

$postObj = new Post();
$interactionObj = new Interaction();

// Cek apakah ada parameter ID post di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: home.php");
    exit;
}

$post_id = $_GET['id'];
$main_post = $postObj->getPostById($post_id);

// Jika post tidak ditemukan / sudah dihapus
if (!$main_post) {
    echo "<h2>Meow-af, Postingan tidak ditemukan!</h2><a href='home.php'>Kembali</a>";
    exit;
}

// Penangkap aksi hapus (Meow Utama maupun Balasan)
if (isset($_GET['delete_id']) && isset($_SESSION['user_id'])) {
    $id_to_delete = $_GET['delete_id'];
    $user_id = $_SESSION['user_id'];

    $postObj->deletePost($id_to_delete, $user_id);
    Flash::set('success', 'Meow berhasil dihapus!');

    if ($id_to_delete == $post_id) {
        header("Location: home.php");
    } else {
        header("Location: post_detail.php?id=" . $post_id);
    }
    exit;
}

// Menangkap proses submit balasan (Reply)
if (isset($_POST['submit_reply']) && isset($_SESSION['user_id'])) {
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    if ($postObj->createReply($user_id, $content, $post_id)) {
        header("Location: post_detail.php?id=" . $post_id);
        exit;
    }
}

// Penangkap aksi Like khusus di halaman ini
if (isset($_GET['like_id'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Silakan login untuk menyukai!'); window.location.href='login.php';</script>";
        exit;
    }
    $interactionObj->toggleLike($_SESSION['user_id'], $_GET['like_id']);
    header("Location: post_detail.php?id=" . $post_id);
    exit;
}

// Penangkap aksi Save khusus di halaman ini
if (isset($_GET['save_id'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Silakan login untuk menyimpan!'); window.location.href='login.php';</script>";
        exit;
    }
    $interactionObj->toggleSave($_SESSION['user_id'], $_GET['save_id']);
    header("Location: post_detail.php?id=" . $post_id);
    exit;
}

// Ambil semua balasan
$replies = $postObj->getRepliesByPostId($post_id);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meow by <?php echo (isset($main_post['is_ghost']) && $main_post['is_ghost'] == 1) ? 'Ghost 👻' : htmlspecialchars($main_post['name']); ?></title>
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
                <a href="home.php" style="margin-right: 15px;">⬅️</a>
                <span>Meow</span>
            </div>

            <?php
            $flash = Flash::get();
            if ($flash) {
                echo "<div class='flash-msg " . ($flash['type'] == 'success' ? 'success' : 'error') . "'>{$flash['message']}</div>";
            }
            ?>

            <div class="main-post">
                <?php if (isset($main_post['is_ghost']) && $main_post['is_ghost'] == 1): ?>
                    <div class="avatar" style="display: flex; align-items: center; justify-content: center; background: var(--border-color); color: var(--text-muted); font-size: 22px; width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;">
                        <i class="bi bi-ghost"></i>
                    </div>
                <?php else: ?>
                    <a href="profile.php?username=<?php echo urlencode($main_post['username']); ?>">
                        <img src="uploads/avatars/<?php echo $main_post['profile_pic']; ?>" class="avatar" onerror="this.src='uploads/avatars/default_avatar.png'">
                    </a>
                <?php endif; ?>
                <div style="width: 100%; margin-left: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <?php if (isset($main_post['is_ghost']) && $main_post['is_ghost'] == 1): ?>
                                <div>
                                    <h4 style="margin: 0; font-size: 18px; display: inline-block;">Ghost 👻</h4>
                                    <span style="color: var(--text-muted); display: block; font-size: 14px;">@ghost</span>
                                </div>
                            <?php else: ?>
                                <a href="profile.php?username=<?php echo urlencode($main_post['username']); ?>" style="text-decoration: none; color: inherit;">
                                    <h4 style="margin: 0; font-size: 18px; display: inline-block;"><?php echo htmlspecialchars($main_post['name']); ?></h4>
                                    <span style="color: var(--text-muted); display: block; font-size: 14px;">@<?php echo htmlspecialchars($main_post['username']); ?></span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $main_post['user_id']): ?>
                            <a href="post_detail.php?id=<?php echo $post_id; ?>&delete_id=<?php echo $main_post['id']; ?>" onclick="return confirm('Yakin ingin menghapus Meow ini?')" style="color: #ef4444; text-decoration: none; font-size: 12px; font-weight: 600;">🗑️ Hapus</a>
                        <?php endif; ?>
                    </div>
                    <p style="font-size: 18px; line-height: 1.6; margin: 15px 0;"><?php echo htmlspecialchars($main_post['content']); ?></p>

                    <?php if (!empty($main_post['post_image'])): ?>
                        <div class="post-image-wrapper">
                            <img src="uploads/posts/<?php echo $main_post['post_image']; ?>">
                        </div>
                        <div style="margin-top: 8px;">
                            <a href="download.php?file=<?php echo urlencode($main_post['post_image']); ?>" class="download-link">📥 Download Gambar</a>
                        </div>
                    <?php endif; ?>

                    <?php
                    $like_count = $interactionObj->getLikeCount($main_post['id']);
                    $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                    $is_liked = $interactionObj->isLikedByUser($current_user_id, $main_post['id']);
                    $is_saved = $interactionObj->isSavedByUser($current_user_id, $main_post['id']);
                    ?>
                    <div class="post-actions">
                        <a href="post_detail.php?id=<?php echo $main_post['id']; ?>&like_id=<?php echo $main_post['id']; ?>" class="post-action-btn <?php echo $is_liked ? 'liked' : ''; ?>">
                            <?php echo $is_liked ? '❤️' : '🤍'; ?>
                            <span><?php echo $like_count; ?></span>
                        </a>

                        <a href="post_detail.php?id=<?php echo $main_post['id']; ?>&save_id=<?php echo $main_post['id']; ?>" class="post-action-btn <?php echo $is_saved ? 'saved' : ''; ?>" style="color: <?php echo $is_saved ? 'var(--primary)' : 'inherit'; ?>;">
                            <?php echo $is_saved ? '🔖' : '📑'; ?> <span><?php echo $is_saved ? 'Tersimpan' : 'Simpan'; ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="reply-form">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="">
                        <textarea name="content" rows="2" placeholder="Balas Meow ini..." required></textarea>
                        <button type="submit" name="submit_reply" class="btn-meow">Balas</button>
                        <div style="clear:both;"></div>
                    </form>
                <?php else: ?>
                    <p style="text-align:center; color:#888; margin:0;">Silakan login untuk membalas.</p>
                <?php endif; ?>
            </div>

            <?php foreach ($replies as $reply): ?>
                <div class="feed-post">
                    <a href="profile.php?username=<?php echo urlencode($reply['username']); ?>">
                        <img src="uploads/avatars/<?php echo $reply['profile_pic']; ?>" class="avatar" onerror="this.src='uploads/avatars/default_avatar.png'">
                    </a>
                    <div style="width: 100%;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <a href="profile.php?username=<?php echo urlencode($reply['username']); ?>" style="text-decoration: none; color: inherit;">
                                <h4 style="margin: 0; display: inline-block;"><?php echo htmlspecialchars($reply['name']); ?></h4>
                                <span style="color: var(--text-muted); font-size: 14px;">@<?php echo htmlspecialchars($reply['username']); ?></span>
                            </a>

                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id']): ?>
                                <a href="post_detail.php?id=<?php echo $post_id; ?>&delete_id=<?php echo $reply['id']; ?>" onclick="return confirm('Yakin ingin menghapus balasan ini?')" style="color: #ef4444; text-decoration: none; font-size: 12px; font-weight: 600;">🗑️ Hapus</a>
                            <?php endif; ?>
                        </div>

                        <p><?php echo htmlspecialchars($reply['content']); ?></p>

                        <?php if (!empty($reply['post_image'])): ?>
                            <div class="post-image-wrapper">
                                <img src="uploads/posts/<?php echo $reply['post_image']; ?>">
                            </div>
                            <div style="margin-top: 8px;">
                                <a href="download.php?file=<?php echo urlencode($reply['post_image']); ?>" class="download-link">📥 Download Gambar</a>
                            </div>
                        <?php endif; ?>
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

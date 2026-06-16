<?php
session_start();
// Ambil data cookie tema, jika belum disetel default-nya adalah 'light'
$theme_preference = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// Proteksi halaman: Pengunjung Wajib Login!
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'classes/Post.php';
require_once 'classes/Interaction.php';

$postObj = new Post();
$interactionObj = new Interaction();

$current_user_id = $_SESSION['user_id'];

// Aksi Hapus Meow (dari timeline Saved)
if (isset($_GET['delete_id'])) {
    $post_id = $_GET['delete_id'];
    $postObj->deletePost($post_id, $current_user_id);
    header("Location: saved.php");
    exit;
}

// Aksi Like / Unlike dari timeline Saved
if (isset($_GET['like_id'])) {
    $interactionObj->toggleLike($current_user_id, $_GET['like_id']);
    header("Location: saved.php");
    exit;
}

// Aksi Unsave dari timeline Saved
if (isset($_GET['save_id'])) {
    $interactionObj->toggleSave($current_user_id, $_GET['save_id']);
    header("Location: saved.php");
    exit;
}

// Ambil semua postingan yang disimpan (bookmarked) oleh user
$all_posts = $interactionObj->getSavedPostsByUser($current_user_id);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved / Meower</title>
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
            <div class="header">Saved Posts 🔖</div>

            <?php if (empty($all_posts)): ?>
                <div style="padding: 60px 20px; text-align: center; color: var(--text-muted);">
                    <i class="bi bi-bookmark" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.6;"></i>
                    <p style="font-size: 16px; font-weight: 600; margin: 0;">Belum ada Meow yang kamu simpan.</p>
                    <span style="font-size: 14px; opacity: 0.8; display: block; margin-top: 5px;">Postingan yang kamu simpan akan muncul di sini.</span>
                </div>
            <?php else: ?>
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

                                <?php if ($post['user_id'] == $current_user_id): ?>
                                    <a href="saved.php?delete_id=<?php echo $post['id']; ?>" onclick="return confirm('Yakin ingin menghapus Meow ini?')" style="color: #ef4444; text-decoration: none; font-size: 12px; font-weight: 600;">🗑️ Hapus</a>
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
                            $is_liked = $interactionObj->isLikedByUser($current_user_id, $post['id']);
                            $is_saved = $interactionObj->isSavedByUser($current_user_id, $post['id']);
                            ?>

                            <div class="post-actions">
                                <a href="saved.php?like_id=<?php echo $post['id']; ?>#post-<?php echo $post['id']; ?>" class="post-action-btn <?php echo $is_liked ? 'liked' : ''; ?>">
                                    <?php echo $is_liked ? '❤️' : '🤍'; ?>
                                    <span><?php echo $like_count; ?></span>
                                </a>

                                <a href="post_detail.php?id=<?php echo $post['id']; ?>" class="post-action-btn">
                                    💬 <span>Balas</span>
                                </a>

                                <a href="saved.php?save_id=<?php echo $post['id']; ?>#post-<?php echo $post['id']; ?>" class="post-action-btn <?php echo $is_saved ? 'saved' : ''; ?>" style="color: <?php echo $is_saved ? 'var(--primary)' : 'inherit'; ?>;">
                                    <?php echo $is_saved ? '🔖' : '📑'; ?> <span><?php echo $is_saved ? 'Tersimpan' : 'Simpan'; ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

        <div class="right-col">
            <?php include 'components/widget.php'; ?>
        </div>

    </div>

</body>

</html>

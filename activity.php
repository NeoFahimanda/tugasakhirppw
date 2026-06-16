<?php
session_start();
// Ambil data cookie tema, jika belum disetel default-nya adalah 'light'
$theme_preference = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// Proteksi halaman: Pengunjung Wajib Login!
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'classes/Database.php';
$db = new Database();
$conn = $db->getConnection();

$current_user_id = $_SESSION['user_id'];

// 1. Ambil data Likes pada postingan saya
$query_likes = "SELECT 'like' as type, likes.created_at, users.name, users.username, users.profile_pic, posts.content as post_content, posts.id as post_id, posts.is_ghost
                FROM likes
                JOIN posts ON likes.post_id = posts.id
                JOIN users ON likes.user_id = users.id
                WHERE posts.user_id = '$current_user_id' AND likes.user_id != '$current_user_id'";
$res_likes = $conn->query($query_likes);
$activities = [];

if ($res_likes && $res_likes->num_rows > 0) {
    while ($row = $res_likes->fetch_assoc()) {
        $activities[] = $row;
    }
}

// 2. Ambil data Replies pada postingan saya
$query_replies = "SELECT 'reply' as type, replies.created_at, users.name, users.username, users.profile_pic, replies.content as reply_content, replies.parent_id as post_id, replies.is_ghost
                  FROM posts as replies
                  JOIN posts ON replies.parent_id = posts.id
                  JOIN users ON replies.user_id = users.id
                  WHERE posts.user_id = '$current_user_id' AND replies.user_id != '$current_user_id'";
$res_replies = $conn->query($query_replies);

if ($res_replies && $res_replies->num_rows > 0) {
    while ($row = $res_replies->fetch_assoc()) {
        $activities[] = $row;
    }
}

// Urutkan aktivitas secara kronologis descending (terbaru di atas)
usort($activities, function ($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity / Meower</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .activity-list {
            display: flex;
            flex-direction: column;
        }
        .activity-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
            text-decoration: none;
            color: inherit;
        }
        .activity-item:hover {
            background-color: var(--sidebar-hover-bg);
        }
        .activity-badge {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
            position: absolute;
            bottom: -4px;
            right: -4px;
            border: 2px solid var(--bg-card);
        }
        .activity-badge.like {
            background-color: #ef4444;
        }
        .activity-badge.reply {
            background-color: var(--primary);
        }
    </style>
</head>

<body class="<?php echo $theme_preference == 'dark' ? 'dark-mode' : ''; ?>">

    <div class="layout-container">

        <div class="left-col">
            <?php include 'components/sidebar.php'; ?>
        </div>

        <div class="mid-col">
            <div class="header">Activity ⚡</div>

            <div class="activity-list">
                <?php if (empty($activities)): ?>
                    <div style="padding: 60px 20px; text-align: center; color: var(--text-muted);">
                        <i class="bi bi-lightning-charge" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.6;"></i>
                        <p style="font-size: 16px; font-weight: 600; margin: 0;">Belum ada aktivitas baru.</p>
                        <span style="font-size: 14px; opacity: 0.8; display: block; margin-top: 5px;">Aktivitas menyukai dan membalas kirimanmu akan muncul di sini.</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($activities as $act): ?>
                        <a href="post_detail.php?id=<?php echo $act['post_id']; ?>" class="activity-item">
                            <div style="position: relative; flex-shrink: 0;">
                                <img src="uploads/avatars/<?php echo $act['profile_pic']; ?>" class="avatar" alt="ava" onerror="this.src='uploads/avatars/default_avatar.png'" style="width: 44px; height: 44px; margin: 0;">
                                <?php if ($act['type'] == 'like'): ?>
                                    <span class="activity-badge like">❤️</span>
                                <?php else: ?>
                                    <span class="activity-badge reply">💬</span>
                                <?php endif; ?>
                            </div>

                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 10px;">
                                    <p style="margin: 0; font-size: 15px; line-height: 1.4; color: var(--text-main);">
                                        <strong><?php echo htmlspecialchars($act['name']); ?></strong> 
                                        <span style="color: var(--text-muted);">@<?php echo htmlspecialchars($act['username']); ?></span>
                                    </p>
                                    <span style="color: var(--text-muted); font-size: 12px; flex-shrink: 0;"><?php echo date('d M H:i', strtotime($act['created_at'])); ?></span>
                                </div>
                                <p style="margin: 4px 0 0 0; font-size: 14px; color: var(--text-muted); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                    <?php if ($act['type'] == 'like'): ?>
                                        menyukai postinganmu: "<?php echo htmlspecialchars($act['post_content']); ?>"
                                    <?php else: ?>
                                        membalas: "<?php echo htmlspecialchars($act['reply_content']); ?>"
                                    <?php endif; ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="right-col">
            <?php include 'components/widget.php'; ?>
        </div>

    </div>

</body>

</html>

<?php
session_start();
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

        .mid-col {
            width: 50%;
            background: white;
        }

        .right-col {
            width: 25%;
            padding: 20px;
            border-left: 1px solid #ddd;
            background: white;
        }

        .header-title {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 20px;
            position: sticky;
            top: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 10;
        }

        /* Tampilan Profil (Banner & Avatar) */
        .profile-banner {
            width: 100%;
            height: 200px;
            background-color: #ccc;
            background-image: url('uploads/headers/<?php echo $profile_data['header_pic']; ?>');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .profile-info {
            padding: 20px;
            position: relative;
            border-bottom: 1px solid #ddd;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            background-color: white;
            object-fit: cover;
            position: absolute;
            top: -60px;
            left: 20px;
        }

        .profile-text {
            margin-top: 60px;
        }

        .profile-text h2 {
            margin: 0;
            font-size: 22px;
        }

        .profile-text span {
            color: #888;
        }

        .profile-text p {
            margin-top: 15px;
            line-height: 1.5;
            color: #333;
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
    </style>
</head>

<body>

    <div class="layout-container">

        <div class="left-col">
            <?php include 'components/sidebar.php'; ?>
        </div>

        <div class="mid-col">
            <div class="header-title">
                <a href="home.php" style="text-decoration:none; color:black; margin-right:15px;">⬅️</a>
                <?php echo htmlspecialchars($profile_data['name']); ?>
            </div>

            <div class="profile-banner"></div>
            <div class="profile-info">
                <img src="uploads/avatars/<?php echo $profile_data['profile_pic']; ?>" class="profile-avatar" alt="Avatar">

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profile_data['id']): ?>
                    <a href="edit_profile.php" style="float:right; padding:8px 15px; border:1px solid #ddd; border-radius:20px; text-decoration:none; color:black; font-weight:bold;">Edit Profile</a>
                <?php endif; ?>

                <div class="profile-text">
                    <h2><?php echo htmlspecialchars($profile_data['name']); ?></h2>
                    <span>@<?php echo htmlspecialchars($profile_data['username']); ?></span>
                    <p><?php echo htmlspecialchars($profile_data['bio'] ?? 'Belum ada bio, Meow...'); ?></p>
                </div>
            </div>

            <?php if (count($user_posts) > 0): ?>
                <?php foreach ($user_posts as $post): ?>
                    <div class="feed-post">
                        <a href="profile.php?username=<?php echo urlencode($post['username']); ?>">
                            <img src="uploads/avatars/<?php echo $post['profile_pic']; ?>" class="avatar">
                        </a>
                        <div class="post-content" style="width: 100%;">

                            <div>
                                <a href="profile.php?username=<?php echo urlencode($post['username']); ?>" style="text-decoration: none; color: inherit;">
                                    <h4 style="margin: 0; display: inline-block;"><?php echo htmlspecialchars($post['name']); ?></h4>
                                    <span style="color: #888; font-size: 14px;">@<?php echo htmlspecialchars($post['username']); ?></span>
                                </a>

                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                    <a href="profile.php?username=<?php echo urlencode($username); ?>&delete_id=<?php echo $post['id']; ?>" onclick="return confirm('Yakin ingin menghapus Meow ini?')" style="color: red; text-decoration: none; font-size: 12px; float: right;">🗑️ Hapus</a>
                                <?php endif; ?>
                            </div>

                            <a href="post_detail.php?id=<?php echo $post['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                                <p style="margin: 10px 0 0 0; line-height: 1.5;"><?php echo htmlspecialchars($post['content']); ?></p>
                            </a>

                            <?php
                            $like_count = $interactionObj->getLikeCount($post['id']);
                            $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                            $is_liked = $interactionObj->isLikedByUser($current_user_id, $post['id']);
                            ?>

                            <div style="margin-top: 15px; display: flex; gap: 20px;">
                                <a href="profile.php?username=<?php echo urlencode($username); ?>&like_id=<?php echo $post['id']; ?>" style="text-decoration: none; font-size: 14px; color: #555;">
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
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

// Ambil semua balasan
$replies = $postObj->getRepliesByPostId($post_id);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Meow by <?php echo htmlspecialchars($main_post['name']); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            transition: background 0.3s, color 0.3s;
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

        .main-post {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            gap: 15px;
            background: #fffaf5;
        }

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

        .reply-form {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            background: #fafafa;
        }

        .reply-form textarea {
            width: 100%;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            font-size: 16px;
            resize: none;
            box-sizing: border-box;
        }

        .btn-meow {
            background: #ff914d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            float: right;
            margin-top: 10px;
        }

        .flash-msg {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 20px 0 20px;
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

        /* === STYLING UNTUK INDIKATOR DARK MODE DI POST DETAIL === */
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

        body.dark-mode .header-title,
        body.dark-mode .reply-form {
            background-color: #15202b;
            border-color: #38444d;
            color: #ffffff;
        }

        body.dark-mode .main-post {
            background-color: #1e2d3b;
            border-color: #38444d;
        }

        body.dark-mode .feed-post {
            border-color: #38444d;
        }

        body.dark-mode .main-post h4,
        body.dark-mode .feed-post h4,
        body.dark-mode .left-col a {
            color: #ffffff;
        }

        body.dark-mode .reply-form textarea {
            background-color: #192734;
            border-color: #38444d;
            color: #ffffff;
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
            <div class="header-title">
                <a href="home.php" style="text-decoration:none; color:inherit; margin-right:15px;">⬅ Meow</a>
            </div>

            <?php
            $flash = Flash::get();
            if ($flash) {
                echo "<div class='flash-msg " . ($flash['type'] == 'success' ? 'success' : 'error') . "'>{$flash['message']}</div>";
            }
            ?>

            <div class="main-post">
                <a href="profile.php?username=<?php echo urlencode($main_post['username']); ?>">
                    <img src="uploads/avatars/<?php echo $main_post['profile_pic']; ?>" class="avatar" onerror="this.src='uploads/avatars/default_avatar.png'">
                </a>
                <div style="width: 100%;">
                    <div style="display: flow-root;">
                        <h4 style="margin: 0; font-size: 18px; display: inline-block;"><?php echo htmlspecialchars($main_post['name']); ?></h4>

                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $main_post['user_id']): ?>
                            <a href="post_detail.php?id=<?php echo $post_id; ?>&delete_id=<?php echo $main_post['id']; ?>" onclick="return confirm('Yakin ingin menghapus Meow ini?')" style="color: red; text-decoration: none; font-size: 12px; float: right;">🗑️ Hapus</a>
                        <?php endif; ?>
                    </div>
                    <span style="color: #888;">@<?php echo htmlspecialchars($main_post['username']); ?></span>
                    <p style="font-size: 20px; line-height: 1.5; margin: 10px 0;"><?php echo htmlspecialchars($main_post['content']); ?></p>

                    <?php if (!empty($main_post['post_image'])): ?>
                        <div style="margin-top: 10px; margin-bottom: 15px;">
                            <img src="uploads/posts/<?php echo $main_post['post_image']; ?>" style="max-width: 100%; max-height: 350px; border-radius: 8px; object-fit: cover; border: 1px solid #eee;">
                            <div style="margin-top: 5px;">
                                <a href="download.php?file=<?php echo urlencode($main_post['post_image']); ?>" style="text-decoration: none; font-size: 12px; color: #ff914d; font-weight: bold;">Download Gambar</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    $like_count = $interactionObj->getLikeCount($main_post['id']);
                    $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                    $is_liked = $interactionObj->isLikedByUser($current_user_id, $main_post['id']);
                    ?>
                    <div style="margin-top: 15px;">
                        <a href="post_detail.php?id=<?php echo $main_post['id']; ?>&like_id=<?php echo $main_post['id']; ?>" style="text-decoration: none; color: #555;">
                            <?php echo $is_liked ? '❤️' : '🤍'; ?>
                            <span style="<?php echo $is_liked ? 'color: red; font-weight: bold;' : ''; ?>"><?php echo $like_count; ?></span>
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
                        <div>
                            <h4 style="margin: 0; display: inline-block;"><?php echo htmlspecialchars($reply['name']); ?></h4>
                            <span style="color: #888;">@<?php echo htmlspecialchars($reply['username']); ?></span>

                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id']): ?>
                                <a href="post_detail.php?id=<?php echo $post_id; ?>&delete_id=<?php echo $reply['id']; ?>" onclick="return confirm('Yakin ingin menghapus balasan ini?')" style="color: red; text-decoration: none; font-size: 12px; float: right;">🗑️ Hapus</a>
                            <?php endif; ?>
                        </div>

                        <p style="margin: 10px 0 0 0; line-height: 1.5;"><?php echo htmlspecialchars($reply['content']); ?></p>

                        <?php if (!empty($reply['post_image'])): ?>
                            <div style="margin-top: 10px;">
                                <img src="uploads/posts/<?php echo $reply['post_image']; ?>" style="max-width: 100%; max-height: 300px; border-radius: 8px; object-fit: cover; border: 1px solid #eee;">
                                <div style="margin-top: 5px;">
                                    <a href="download.php?file=<?php echo urlencode($reply['post_image']); ?>" style="text-decoration: none; font-size: 12px; color: #ff914d; font-weight: bold;">Download Gambar</a>
                                </div>
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
<?php
session_start();
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

$all_posts = $postObj->getAllPosts();

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
    </style>
</head>

<body>

    <div class="layout-container">

        <div class="left-col">
            <?php include 'components/sidebar.php'; ?>
        </div>

        <div class="mid-col">
            <div class="header">Home</div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="post-form">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <textarea name="content" rows="3" placeholder="Apa yang sedang kamu pikirkan, Meow?" required></textarea>
                        <button type="submit" name="submit_post" class="btn-meow">Meow</button>
                        <input type="file" name="post_img" accept="image/png, image/jpeg" class="btn-upload">

                    </form>
                </div>
            <?php else: ?>
                <div class="post-form" style="text-align:center; color:#888;">
                    <p>Silakan login untuk membagikan Meow-mu.</p>
                </div>
            <?php endif; ?>



            <?php foreach ($all_posts as $post): ?>
                <div class="feed-post">
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
                                        <a href="download.php?file=<?php echo urlencode($post['post_image']); ?>" style="text-decoration: none; font-size: 12px; color: #ff914d; font-weight: bold;">📥 Download Gambar</a>
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
                            <a href="home.php?like_id=<?php echo $post['id']; ?>" style="text-decoration: none; font-size: 14px; color: #555;">
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
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'classes/User.php';
require_once 'classes/Flash.php';

$userObj = new User();
$user_id = $_SESSION['user_id'];
$user_data = $userObj->getUserById($user_id);

if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $bio = $_POST['bio'];

    // Default gambar lama
    $profile_pic = $user_data['profile_pic'];
    $header_pic = $user_data['header_pic'];

    // Upload Avatar
    if (!empty($_FILES['avatar']['name'])) {
        $avatar_name = time() . "_" . $_FILES['avatar']['name'];
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], "uploads/avatars/" . $avatar_name)) {
            $profile_pic = $avatar_name;
        }
    }

    // Upload Header
    if (!empty($_FILES['header']['name'])) {
        $header_name = time() . "_" . $_FILES['header']['name'];
        if (move_uploaded_file($_FILES['header']['tmp_name'], "uploads/headers/" . $header_name)) {
            $header_pic = $header_name;
        }
    }

    // Eksekusi Update
    if ($userObj->updateProfile($user_id, $name, $bio, $profile_pic, $header_pic)) {
        $_SESSION['name'] = $name;
        Flash::set('success', 'Profil berhasil diperbarui, Meow!');
    } else {
        Flash::set('error', 'Gagal memperbarui profil, Meow-af.');
    }

    header("Location: edit_profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile / Meower</title>
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

        /* Layout Kolom (Sama seperti home.php) */
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

        .header {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 20px;
        }

        /* Form Edit Profil */
        .edit-form {
            padding: 20px;
        }

        .edit-form label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            color: #555;
        }

        .edit-form input[type="text"],
        .edit-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .edit-form input[type="file"] {
            margin-top: 5px;
            font-size: 14px;
        }

        .btn-save {
            background: #ff914d;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            font-size: 16px;
        }

        .btn-save:hover {
            background: #e57c38;
        }

        .msg-box {
            padding: 10px;
            background: #e6ffe6;
            color: #5cb85c;
            border: 1px solid #5cb85c;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        /* Preview Gambar Saat Ini */
        .preview-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            vertical-align: middle;
            margin-right: 10px;
            border: 1px solid #ddd;
        }

        .preview-header {
            width: 100px;
            height: 40px;
            object-fit: cover;
            vertical-align: middle;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <div class="layout-container">

        <div class="left-col">
            <?php include 'components/sidebar.php'; ?>
        </div>

        <div class="mid-col">
            <div class="header">Edit Profile</div>

            <div class="edit-form">
                <?php
                $flash = Flash::get();
                if ($flash) {
                    // Jika flash sukses, class CSS 'success', jika error, class 'error'
                    echo "<div class='flash-msg " . ($flash['type'] == 'success' ? 'success' : 'error') . "'>{$flash['message']}</div>";
                }
                ?>

                <form method="POST" action="" enctype="multipart/form-data">

                    <label>Display Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>

                    <label>Bio</label>
                    <textarea name="bio" rows="4" placeholder="Ceritakan tentang dirimu, Meow..."><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>

                    <label>Foto Profil (Avatar)</label>
                    <img src="uploads/avatars/<?php echo $user_data['profile_pic']; ?>" class="preview-img">
                    <input type="file" name="avatar" accept="image/png, image/jpeg">

                    <label>Foto Sampul (Header)</label>
                    <img src="uploads/headers/<?php echo $user_data['header_pic']; ?>" class="preview-header">
                    <input type="file" name="header" accept="image/png, image/jpeg">

                    <button type="submit" name="update_profile" class="btn-save">Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <div class="right-col">
            <?php include 'components/widget.php'; ?>
        </div>

    </div>

</body>

</html>
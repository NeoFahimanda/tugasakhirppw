<?php
session_start();
// Ambil data cookie tema, jika belum disetel default-nya adalah 'light'
$theme_preference = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// ... sisa require_once dan logika database yang sudah ada ...
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

    // 1. Upload Avatar (Cropped Base64 prioritised)
    if (!empty($_POST['avatar_cropped'])) {
        $avatar_base64 = $_POST['avatar_cropped'];
        // Remove base64 header
        $avatar_base64 = preg_replace('#^data:image/\w+;base64,#i', '', $avatar_base64);
        $avatar_base64 = str_replace(' ', '+', $avatar_base64);
        $avatar_data = base64_decode($avatar_base64);
        
        $avatar_name = time() . "_avatar.jpg";
        $avatar_path = "uploads/avatars/" . $avatar_name;
        
        if (file_put_contents($avatar_path, $avatar_data)) {
            $profile_pic = $avatar_name;
        }
    } else if (!empty($_FILES['avatar']['name'])) {
        $avatar_name = time() . "_" . $_FILES['avatar']['name'];
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], "uploads/avatars/" . $avatar_name)) {
            $profile_pic = $avatar_name;
        }
    }

    // 2. Upload Header (Cropped Base64 prioritised)
    if (!empty($_POST['header_cropped'])) {
        $header_base64 = $_POST['header_cropped'];
        // Remove base64 header
        $header_base64 = preg_replace('#^data:image/\w+;base64,#i', '', $header_base64);
        $header_base64 = str_replace(' ', '+', $header_base64);
        $header_data = base64_decode($header_base64);
        
        $header_name = time() . "_header.jpg";
        $header_path = "uploads/headers/" . $header_name;
        
        if (file_put_contents($header_path, $header_data)) {
            $header_pic = $header_name;
        }
    } else if (!empty($_FILES['header']['name'])) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile / Meower</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Cropper.js CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
</head>

<body class="<?php echo $theme_preference == 'dark' ? 'dark-mode' : ''; ?>">

    <div class="layout-container">

        <div class="left-col">
            <?php include 'components/sidebar.php'; ?>
        </div>

        <div class="mid-col">
            <div class="header-title">
                <a href="profile.php?username=<?php echo urlencode($user_data['username']); ?>" style="margin-right: 15px;">⬅️</a>
                <span>Edit Profile</span>
            </div>

            <div class="edit-form">
                <?php
                $flash = Flash::get();
                if ($flash) {
                    echo "<div class='flash-msg " . ($flash['type'] == 'success' ? 'success' : 'error') . "'>{$flash['message']}</div>";
                }
                ?>

                <form method="POST" action="" enctype="multipart/form-data" id="profileEditForm">
                    <input type="hidden" name="avatar_cropped" id="avatar_cropped_input">
                    <input type="hidden" name="header_cropped" id="header_cropped_input">

                    <div class="form-group">
                        <label>Display Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" rows="4" placeholder="Ceritakan tentang dirimu, Meow..."><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Foto Profil (Avatar)</label>
                        <div class="file-upload-wrapper">
                            <img src="uploads/avatars/<?php echo $user_data['profile_pic']; ?>" class="preview-img" id="avatarPreview" onerror="this.src='uploads/avatars/default_avatar.png'">
                            <label for="avatar" class="custom-file-upload">📷 Pilih Foto Profil</label>
                            <input type="file" name="avatar" id="avatar" accept="image/png, image/jpeg" class="input-file-hidden">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Foto Sampul (Header)</label>
                        <div class="file-upload-wrapper">
                            <img src="uploads/headers/<?php echo $user_data['header_pic']; ?>" class="preview-header" id="headerPreview" onerror="this.src='uploads/headers/default_header.png'">
                            <label for="header" class="custom-file-upload">🖼️ Pilih Foto Sampul</label>
                            <input type="file" name="header" id="header" accept="image/png, image/jpeg" class="input-file-hidden">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
                        <button type="submit" name="update_profile" class="btn-save">Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            <!-- Modal Cropper -->
            <div id="cropperModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.85); align-items: center; justify-content: center; padding: 20px;">
                <div style="background-color: var(--bg-card); border-radius: var(--radius-lg); padding: 24px; max-width: 500px; width: 100%; display: flex; flex-direction: column; gap: 16px; box-shadow: var(--shadow-lg);">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 800;" id="cropperModalTitle">Potong Gambar</h3>
                    <div style="max-height: 350px; overflow: hidden; border-radius: var(--radius-sm); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; background-color: #000;">
                        <img id="cropperImage" style="max-width: 100%; display: block;" src="">
                    </div>
                    <div style="display: flex; justify-content: space-between; gap: 12px; margin-top: 10px;">
                        <button type="button" id="cancelCropBtn" style="padding: 10px 20px; border: 1px solid var(--border-color); background: transparent; color: var(--text-main); font-weight: 700; border-radius: var(--radius-xl); cursor: pointer; flex: 1; transition: all 0.2s;">Batal</button>
                        <button type="button" id="applyCropBtn" style="padding: 10px 20px; border: none; background: var(--primary); color: white; font-weight: 700; border-radius: var(--radius-xl); cursor: pointer; flex: 1; transition: all 0.2s;">Potong & Terapkan</button>
                    </div>
                </div>
            </div>

            <script>
                let cropper = null;
                let currentCropTarget = '';
                const cropperModal = document.getElementById('cropperModal');
                const cropperImage = document.getElementById('cropperImage');
                const cropperModalTitle = document.getElementById('cropperModalTitle');
                const cancelCropBtn = document.getElementById('cancelCropBtn');
                const applyCropBtn = document.getElementById('applyCropBtn');

                // Inputs
                const avatarInput = document.getElementById('avatar');
                const headerInput = document.getElementById('header');

                avatarInput.addEventListener('change', function(e) {
                    handleFileSelect(e, 'avatar');
                });

                headerInput.addEventListener('change', function(e) {
                    handleFileSelect(e, 'header');
                });

                function handleFileSelect(e, target) {
                    const file = e.target.files[0];
                    if (file) {
                        currentCropTarget = target;
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            cropperImage.src = event.target.result;
                            cropperModalTitle.textContent = target === 'avatar' ? 'Potong Foto Profil (1:1)' : 'Potong Foto Sampul (16:5)';
                            cropperModal.style.display = 'flex';
                            
                            if (cropper) {
                                cropper.destroy();
                            }
                            
                            cropper = new Cropper(cropperImage, {
                                aspectRatio: target === 'avatar' ? 1 : 16 / 5,
                                viewMode: 1,
                                autoCropArea: 1,
                                responsive: true,
                                restore: false,
                                checkCrossOrigin: false
                            });
                        };
                        reader.readAsDataURL(file);
                    }
                }

                cancelCropBtn.addEventListener('click', function() {
                    cropperModal.style.display = 'none';
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                    if (currentCropTarget === 'avatar') {
                        avatarInput.value = '';
                    } else {
                        headerInput.value = '';
                    }
                });

                applyCropBtn.addEventListener('click', function() {
                    if (cropper) {
                        let canvasOptions = {};
                        if (currentCropTarget === 'avatar') {
                            canvasOptions = { width: 300, height: 300 };
                        } else {
                            canvasOptions = { width: 800, height: 250 };
                        }
                        
                        const canvas = cropper.getCroppedCanvas(canvasOptions);
                        const base64Data = canvas.toDataURL('image/jpeg', 0.9);
                        
                        if (currentCropTarget === 'avatar') {
                            document.getElementById('avatarPreview').src = base64Data;
                            document.getElementById('avatar_cropped_input').value = base64Data;
                        } else {
                            document.getElementById('headerPreview').src = base64Data;
                            document.getElementById('header_cropped_input').value = base64Data;
                        }
                        
                        cropperModal.style.display = 'none';
                        cropper.destroy();
                        cropper = null;
                    }
                });
            </script>
        </div>

        <div class="right-col">
            <?php include 'components/widget.php'; ?>
        </div>

    </div>

</body>

</html>

<?php
// Pastikan kita menyertakan class User jika belum di-include di file utama
require_once __DIR__ . '/../classes/User.php';
$widgetUserObj = new User();

// Ambil 3 besar user dengan likes terbanyak
$top_meowers = $widgetUserObj->getTopLikedUsers(3);

// Ambil kata kunci pencarian saat ini jika ada
$search_value = isset($_GET['search']) ? $_GET['search'] : '';
?>

<form method="GET" action="home.php" style="margin-bottom: 20px;">
    <div style="position: relative; display: flex; align-items: center;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search_value); ?>" placeholder="Cari Meow..."
            style="width: 100%; padding: 12px 15px; border: 1px solid #e1e8ed; background-color: #f7f9fa; border-radius: 30px; outline: none; font-size: 15px; box-sizing: border-box;">
    </div>
</form>

<div class="widget-box" style="border-radius: 16px; padding: 15px; margin-top: 15px; font-family: inherit;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 800;">Popular Meowers 🐾</h3>

    <?php if (!empty($top_meowers)): ?>
        <?php foreach ($top_meowers as $index => $top_user): ?>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; font-size: 14px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: bold; color: #ff914d; font-size: 16px;">#<?php echo $index + 1; ?></span>

                    <img src="uploads/avatars/<?php echo $top_user['profile_pic']; ?>"
                        style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;"
                        onerror="this.src='uploads/avatars/default_avatar.png'">

                    <div>
                        <a href="profile.php?username=<?php echo urlencode($top_user['username']); ?>" style="text-decoration: none; color: #657786; font-weight: bold; display: block;">
                            <?php echo htmlspecialchars($top_user['name']); ?>
                        </a>
                        <span style="color: #657786; font-size: 12px;">@<?php echo htmlspecialchars($top_user['username']); ?></span>
                    </div>
                </div>

                <div style="text-align: right;">
                    <span style="font-weight: bold; color: #e0245e;">❤️ <?php echo $top_user['total_likes']; ?></span>
                    <span style="display: block; font-size: 10px; color: #657786;">Likes</span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #657786; font-size: 14px; margin: 0; text-align: center;">Belum ada interaksi like, Meow.</p>
    <?php endif; ?>
</div>
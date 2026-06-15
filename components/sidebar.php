<div class="sidebar">
    <h2>🐱 Meower</h2>
    <a href="home.php">🏠 Home</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php?username=<?php echo $_SESSION['username']; ?>">👤 Profile</a>
        <a href="edit_profile.php">⚙️ Settings</a>
        <a href="logout.php" style="color: red; margin-top: 20px;">🚪 Logout</a>
    <?php else: ?>
        <a href="login.php" style="color: #ff914d; font-weight:bold;">🔑 Login / Daftar</a>
    <?php endif; ?>
</div>
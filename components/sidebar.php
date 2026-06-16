<div class="sidebar">
    <h2>🐱 Meower</h2>
    <a href="home.php">Home</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php?username=<?php echo $_SESSION['username']; ?>">Profile</a>
        <a href="edit_profile.php">Settings</a>
        <a href="logout.php" style="color: red; margin-top: 20px;">Logout</a>
    <?php else: ?>
        <a href="login.php" style="color: #ff914d; font-weight:bold;">Login / Daftar</a>
    <?php endif; ?>

    <button id="theme-toggle" style="margin-top: 25px; padding: 10px 15px; width: 100%; border-radius: 20px; border: 1px solid #ff914d; background: transparent; color: #ff914d; font-weight: bold; cursor: pointer; font-size: 15px;">
        🌓 Ganti Tema
    </button>

    <script>
        const toggleBtn = document.getElementById('theme-toggle');

        toggleBtn.addEventListener('click', () => {
            // Toggle class dark-mode pada tag body
            document.body.classList.toggle('dark-mode');

            // Cek apakah sekarang posisinya dark mode?
            const isDark = document.body.classList.contains('dark-mode');

            // Simpan ke cookie selama 1 tahun (max-age dalam detik)
            document.cookie = "theme=" + (isDark ? "dark" : "light") + "; path=/; max-age=31536000";
        });
    </script>
</div>
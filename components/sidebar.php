<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
$is_home = ($current_page == 'home.php' && !isset($_GET['search']) && !isset($_GET['feed']));
$is_search = ($current_page == 'home.php' && isset($_GET['search']) && $_GET['search'] != 'technology');
$is_profile = ($current_page == 'profile.php');
$is_edit_profile = ($current_page == 'edit_profile.php');
$is_messages = ($current_page == 'messages.php');
$is_activity = ($current_page == 'activity.php');
$is_insights = ($current_page == 'insights.php');
$is_saved = ($current_page == 'saved.php');
$is_ghost_feed = ($current_page == 'home.php' && isset($_GET['feed']) && $_GET['feed'] == 'ghost');
$is_tech_feed = ($current_page == 'home.php' && isset($_GET['search']) && $_GET['search'] == 'technology');
?>
<div class="sidebar">
    <!-- Brand Logo (Threads spiral style) -->
    <a href="home.php" class="sidebar-logo">
        <svg viewBox="0 0 24 24">
            <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10c2.785 0 5.439-1.123 7.385-3.116a1 1 0 1 0-1.458-1.37A7.957 7.957 0 0 1 12 20c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8v1.309c0 .942-.294 1.341-.572 1.503-.186.108-.5.188-.928.188-.707 0-1.293-.41-1.479-1.036l-.372-1.25A5.962 5.962 0 0 1 12 14c-2.757 0-5-2.243-5-5s2.243-5 5-5 5 2.243 5 5v3.131c0 2.215 1.583 3.869 3.807 3.869.839 0 1.637-.23 2.308-.622.95-.553 1.516-1.597 1.516-2.937V12c0-5.523-4.477-10-10-10zm0 10c-1.654 0-3-1.346-3-3s1.346-3 3-3 3 1.346 3 3-1.346 3-3 3z"/>
        </svg>
        <span>meower</span>
    </a>

    <!-- Main Navigation -->
    <a href="home.php" class="sidebar-link <?php echo $is_home ? 'active' : ''; ?>">
        <i class="bi bi-house"></i>
        <span>For you</span>
    </a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="home.php?focus=post" class="sidebar-link" id="new-thread-btn">
            <i class="bi bi-plus-lg"></i>
            <span>New thread</span>
        </a>
    <?php endif; ?>

    <a href="home.php?focus=search" class="sidebar-link <?php echo $is_search ? 'active' : ''; ?>" id="sidebar-search-btn">
        <i class="bi bi-search"></i>
        <span>Search</span>
    </a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="messages.php" class="sidebar-link <?php echo $is_messages ? 'active' : ''; ?>">
            <i class="bi bi-chat"></i>
            <span>Messages</span>
        </a>

        <a href="activity.php" class="sidebar-link <?php echo $is_activity ? 'active' : ''; ?>">
            <i class="bi bi-heart"></i>
            <span>Activity</span>
        </a>

        <a href="profile.php?username=<?php echo $_SESSION['username']; ?>" class="sidebar-link <?php echo ($is_profile || $is_edit_profile) ? 'active' : ''; ?>">
            <i class="bi bi-person"></i>
            <span>Profile</span>
        </a>
    <?php else: ?>
        <a href="login.php" class="sidebar-link" style="color: var(--primary);">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>Login / Daftar</span>
        </a>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="insights.php" class="sidebar-link <?php echo $is_insights ? 'active' : ''; ?>">
            <i class="bi bi-bar-chart"></i>
            <span>Insights</span>
        </a>

        <a href="saved.php" class="sidebar-link <?php echo $is_saved ? 'active' : ''; ?>">
            <i class="bi bi-bookmark"></i>
            <span>Saved</span>
        </a>
    <?php endif; ?>

    <!-- Feeds Section (Muted category feed links) -->
    <div class="feeds-section">
        <div class="feeds-header">
            <span class="feeds-title">Feeds</span>
            <a href="#" class="feeds-edit" onclick="event.preventDefault();">Edit</a>
        </div>
        <a href="home.php?feed=ghost" class="sub-feed-link <?php echo $is_ghost_feed ? 'active' : ''; ?>">
            <span>Ghost posts</span>
            <i class="bi bi-ghost"></i>
        </a>
    </div>

    <!-- Bottom Menu Trigger -->
    <div class="sidebar-bottom">
        <!-- Dropdown Menu Popover -->
        <div class="more-popover" id="more-popover">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="edit_profile.php" class="popover-item">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
                <div class="popover-divider"></div>
            <?php endif; ?>
            <button type="button" class="popover-item" id="theme-toggle-btn">
                <i class="bi bi-moon-stars"></i>
                <span>Ganti Tema</span>
            </button>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="popover-divider"></div>
                <a href="logout.php" class="popover-item logout">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>Logout</span>
                </a>
            <?php endif; ?>
        </div>

        <button type="button" class="sidebar-link" id="more-menu-btn" style="margin-bottom: 0;">
            <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; stroke: currentColor; stroke-width: 2.2; fill: none; stroke-linecap: round;">
                <line x1="3" y1="8" x2="21" y2="8" />
                <line x1="3" y1="16" x2="15" y2="16" />
            </svg>
            <span>More</span>
        </button>
    </div>
</div>

<!-- Global New Thread Modal (Instagram Threads Style) -->
<div class="modal-backdrop" id="newThreadModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>New thread</h3>
            <button type="button" class="modal-close-btn" id="closeModalBtn">✕</button>
        </div>
        <form method="POST" action="home.php" enctype="multipart/form-data" class="modal-form" id="modalMeowForm">
            <textarea name="content" rows="4" placeholder="Apa yang sedang kamu pikirkan, Meow?" required class="modal-textarea"></textarea>
            
            <div class="preview-container" id="modalPreviewWrapper">
                <button type="button" class="btn-remove-preview" id="modalCancelPreview">✕</button>
                <img src="" id="modalImagePreview" alt="Preview Gambar">
            </div>

            <div class="modal-footer">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <label for="modal_post_img" class="custom-file-upload" style="margin: 0;">
                        🖼️ Tambah Gambar
                    </label>
                    <input type="file" name="post_img" id="modal_post_img" accept="image/png, image/jpeg" class="input-file-hidden">

                    <label style="display: flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 700; color: var(--text-muted); cursor: pointer; user-select: none;">
                        <input type="checkbox" name="is_ghost" value="1" style="width: 16px; height: 16px; accent-color: var(--primary);">
                        Post as Ghost 👻
                    </label>
                </div>
                <button type="submit" name="submit_post" class="btn-meow">Meow</button>
            </div>
        </form>
    </div>
</div>

<script>
    // More Popover Toggle
    const moreBtn = document.getElementById('more-menu-btn');
    const popover = document.getElementById('more-popover');

    if (moreBtn && popover) {
        moreBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            popover.classList.toggle('show');
        });

        // Close popover on click outside
        document.addEventListener('click', (e) => {
            if (!popover.contains(e.target) && e.target !== moreBtn && !moreBtn.contains(e.target)) {
                popover.classList.remove('show');
            }
        });
    }

    // Theme Toggle inside Popover
    const themeBtn = document.getElementById('theme-toggle-btn');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            document.cookie = "theme=" + (isDark ? "dark" : "light") + "; path=/; max-age=31536000";
            
            // Update icon inside theme button
            const icon = themeBtn.querySelector('i');
            if (icon) {
                icon.className = isDark ? 'bi bi-sun' : 'bi bi-moon-stars';
            }
        });
        
        // Initial icon update on load
        const setupThemeIcon = () => {
            const isDark = document.body.classList.contains('dark-mode');
            const icon = themeBtn.querySelector('i');
            if (icon) {
                icon.className = isDark ? 'bi bi-sun' : 'bi bi-moon-stars';
            }
        };

        if (document.readyState === 'loading') {
            window.addEventListener('DOMContentLoaded', setupThemeIcon);
        } else {
            setupThemeIcon();
        }
    }

    // "New thread" Modal Toggle & Handlers
    const newThreadBtn = document.getElementById('new-thread-btn');
    const newThreadModal = document.getElementById('newThreadModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    
    if (newThreadBtn && newThreadModal) {
        newThreadBtn.addEventListener('click', (e) => {
            // Jika ada form bawaan di halaman (misal home.php), kita prioritaskan modal
            e.preventDefault();
            newThreadModal.style.display = 'flex';
            const textarea = newThreadModal.querySelector('textarea');
            if (textarea) textarea.focus();
        });
    }
    
    if (closeModalBtn && newThreadModal) {
        closeModalBtn.addEventListener('click', () => {
            newThreadModal.style.display = 'none';
        });
        
        // Close on background backdrop click
        newThreadModal.addEventListener('click', (e) => {
            if (e.target === newThreadModal) {
                newThreadModal.style.display = 'none';
            }
        });
    }

    // Modal Image Preview logic
    const modalFileInput = document.getElementById('modal_post_img');
    const modalPreviewWrapper = document.getElementById('modalPreviewWrapper');
    const modalImagePreview = document.getElementById('modalImagePreview');
    const modalCancelPreview = document.getElementById('modalCancelPreview');

    if (modalFileInput) {
        modalFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function() {
                    modalImagePreview.setAttribute('src', this.result);
                    modalPreviewWrapper.style.display = 'inline-block';
                });
                reader.readAsDataURL(file);
            }
        });
    }

    if (modalCancelPreview) {
        modalCancelPreview.addEventListener('click', function() {
            modalFileInput.value = "";
            modalImagePreview.setAttribute('src', '');
            modalPreviewWrapper.style.display = 'none';
        });
    }

    // "Search" button scroll/focus helper
    const searchBtn = document.getElementById('sidebar-search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', (e) => {
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                e.preventDefault();
                searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                searchInput.focus();
            }
        });
    }

    // Focus elements on page load (Fix DOMContentLoaded race condition)
    function handleFocusOnLoad() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('focus') === 'post') {
            const textarea = document.querySelector('.post-form textarea');
            if (textarea) {
                setTimeout(() => {
                    textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    textarea.focus();
                }, 300);
            }
        } else if (urlParams.get('focus') === 'search') {
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                setTimeout(() => {
                    searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    searchInput.focus();
                }, 300);
            }
        }
    }

    if (document.readyState === 'loading') {
        window.addEventListener('DOMContentLoaded', handleFocusOnLoad);
    } else {
        handleFocusOnLoad();
    }
</script>
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

// 1. Total Meows (Normal)
$q_meows = $conn->query("SELECT COUNT(*) as total FROM posts WHERE user_id = '$current_user_id' AND parent_id IS NULL AND is_ghost = 0");
$normal_meows = $q_meows->fetch_assoc()['total'];

// 2. Total Ghost Meows
$q_ghost = $conn->query("SELECT COUNT(*) as total FROM posts WHERE user_id = '$current_user_id' AND parent_id IS NULL AND is_ghost = 1");
$ghost_meows = $q_ghost->fetch_assoc()['total'];

$total_meows = $normal_meows + $ghost_meows;

// 3. Total Replies
$q_replies = $conn->query("SELECT COUNT(*) as total FROM posts WHERE user_id = '$current_user_id' AND parent_id IS NOT NULL");
$total_replies = $q_replies->fetch_assoc()['total'];

// 4. Likes Received
$q_likes_rec = $conn->query("SELECT COUNT(likes.id) as total FROM likes JOIN posts ON likes.post_id = posts.id WHERE posts.user_id = '$current_user_id'");
$likes_received = $q_likes_rec->fetch_assoc()['total'];

// 5. Likes Given
$q_likes_given = $conn->query("SELECT COUNT(*) as total FROM likes WHERE user_id = '$current_user_id'");
$likes_given = $q_likes_given->fetch_assoc()['total'];

// 6. Bookmarked Posts
$q_saved = $conn->query("SELECT COUNT(*) as total FROM saved_posts WHERE user_id = '$current_user_id'");
$total_saved = $q_saved->fetch_assoc()['total'];

// Hitung persentase untuk chart
$percent_normal = $total_meows > 0 ? round(($normal_meows / $total_meows) * 100) : 0;
$percent_ghost = $total_meows > 0 ? round(($ghost_meows / $total_meows) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insights / Meower</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            padding: 24px;
        }
        .stat-card {
            background-color: var(--bg-body);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            text-align: center;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
        }
        .stat-val {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
            margin: 0;
        }
        .stat-lbl {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            margin: 8px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .chart-box {
            margin: 0 24px 24px 24px;
            padding: 24px;
            background-color: var(--bg-body);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
        }
        .chart-title {
            font-size: 16px;
            font-weight: 800;
            margin: 0 0 20px 0;
            color: var(--text-main);
        }
        .bar-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .bar-row {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .bar-lbl {
            width: 120px;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
        }
        .bar-track {
            flex: 1;
            height: 16px;
            background-color: var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        .bar-fill {
            height: 100%;
            background-color: var(--primary);
            border-radius: 8px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bar-fill.ghost {
            background-color: #64748b;
        }
        .bar-val {
            width: 50px;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
            text-align: right;
        }
    </style>
</head>

<body class="<?php echo $theme_preference == 'dark' ? 'dark-mode' : ''; ?>">

    <div class="layout-container">

        <div class="left-col">
            <?php include 'components/sidebar.php'; ?>
        </div>

        <div class="mid-col">
            <div class="header">Insights 📊</div>

            <div class="insights-grid">
                <div class="stat-card">
                    <p class="stat-val"><?php echo $total_meows; ?></p>
                    <p class="stat-lbl">Total Meows</p>
                </div>
                <div class="stat-card">
                    <p class="stat-val"><?php echo $likes_received; ?></p>
                    <p class="stat-lbl">Likes Received</p>
                </div>
                <div class="stat-card">
                    <p class="stat-val"><?php echo $total_replies; ?></p>
                    <p class="stat-lbl">Replies Sent</p>
                </div>
                <div class="stat-card">
                    <p class="stat-val"><?php echo $total_saved; ?></p>
                    <p class="stat-lbl">Saved Posts</p>
                </div>
            </div>

            <!-- Chart Box 1 -->
            <div class="chart-box">
                <h3 class="chart-title">Post Types Distribution 📝</h3>
                <div class="bar-container">
                    <div class="bar-row">
                        <span class="bar-lbl">Normal Meows</span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?php echo $percent_normal; ?>%"></div>
                        </div>
                        <span class="bar-val"><?php echo $normal_meows; ?></span>
                    </div>
                    <div class="bar-row">
                        <span class="bar-lbl">Ghost Meows 👻</span>
                        <div class="bar-track">
                            <div class="bar-fill ghost" style="width: <?php echo $percent_ghost; ?>%"></div>
                        </div>
                        <span class="bar-val"><?php echo $ghost_meows; ?></span>
                    </div>
                </div>
            </div>

            <!-- Chart Box 2 -->
            <div class="chart-box">
                <h3 class="chart-title">Likes Comparison ❤️</h3>
                <?php
                $total_likes_combined = $likes_received + $likes_given;
                $percent_likes_rec = $total_likes_combined > 0 ? round(($likes_received / $total_likes_combined) * 100) : 0;
                $percent_likes_giv = $total_likes_combined > 0 ? round(($likes_given / $total_likes_combined) * 100) : 0;
                ?>
                <div class="bar-container">
                    <div class="bar-row">
                        <span class="bar-lbl">Likes Received</span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?php echo $percent_likes_rec; ?>%; background-color: #ef4444;"></div>
                        </div>
                        <span class="bar-val"><?php echo $likes_received; ?></span>
                    </div>
                    <div class="bar-row">
                        <span class="bar-lbl">Likes Given</span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?php echo $percent_likes_giv; ?>%; background-color: #fca5a5;"></div>
                        </div>
                        <span class="bar-val"><?php echo $likes_given; ?></span>
                    </div>
                </div>
            </div>

        </div>

        <div class="right-col">
            <?php include 'components/widget.php'; ?>
        </div>

    </div>

</body>

</html>

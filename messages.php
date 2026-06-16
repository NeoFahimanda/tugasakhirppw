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
require_once 'classes/User.php';

$db = new Database();
$conn = $db->getConnection();
$userObj = new User();

$current_user_id = $_SESSION['user_id'];

// Ambil daftar users untuk dichat (Diurutkan berdasarkan chat terbaru/interaksi terakhir)
$contacts = $userObj->getChatContacts($current_user_id);

// Ambil contact_id aktif dari URL GET
$active_contact_id = isset($_GET['contact_id']) ? (int)$_GET['contact_id'] : null;
$active_contact = null;

if ($active_contact_id) {
    $res_contact = $conn->query("SELECT * FROM users WHERE id = '$active_contact_id'");
    if ($res_contact && $res_contact->num_rows > 0) {
        $active_contact = $res_contact->fetch_assoc();
    }
}

// Proses kirim pesan (POST)
if (isset($_POST['send_message']) && $active_contact_id) {
    $msg_content = trim($_POST['message_text']);
    if (!empty($msg_content)) {
        $msg_content = $conn->real_escape_string($msg_content);
        
        // 1. Simpan pesan pengirim
        $conn->query("INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$current_user_id', '$active_contact_id', '$msg_content')");
        
        header("Location: messages.php?contact_id=" . $active_contact_id);
        exit;
    }
}

// Ambil history chat jika ada kontak aktif
$messages = [];
if ($active_contact_id) {
    $msg_query = "SELECT * FROM messages 
                  WHERE (sender_id = '$current_user_id' AND receiver_id = '$active_contact_id') 
                     OR (sender_id = '$active_contact_id' AND receiver_id = '$current_user_id') 
                  ORDER BY created_at ASC";
    $res_msg = $conn->query($msg_query);
    if ($res_msg && $res_msg->num_rows > 0) {
        while ($row = $res_msg->fetch_assoc()) {
            $messages[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages / Meower</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .messaging-container {
            display: flex;
            height: calc(100vh - 60px);
            background-color: var(--bg-card);
            border-bottom-left-radius: var(--radius-lg);
            border-bottom-right-radius: var(--radius-lg);
            overflow: hidden;
        }
        .contacts-pane {
            width: 320px;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            background-color: var(--bg-card);
        }
        .contacts-list {
            flex: 1;
            overflow-y: auto;
        }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            color: inherit;
            transition: background-color 0.2s ease;
        }
        .contact-item:hover {
            background-color: var(--sidebar-hover-bg);
        }
        .contact-item.active {
            background-color: var(--sidebar-active-bg);
        }
        .chat-pane {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--bg-body);
        }
        .chat-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--bg-card);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .chat-history {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .bubble-msg {
            max-width: 65%;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            font-size: 15px;
            line-height: 1.5;
            word-break: break-word;
        }
        .bubble-msg.sent {
            align-self: flex-end;
            background-color: var(--primary);
            color: white;
            border-bottom-right-radius: 2px;
        }
        .bubble-msg.received {
            align-self: flex-start;
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-bottom-left-radius: 2px;
        }
        .chat-input-area {
            padding: 16px 20px;
            background-color: var(--bg-card);
            border-top: 1px solid var(--border-color);
        }
        .chat-form {
            display: flex;
            gap: 12px;
        }
        .chat-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            background-color: var(--bg-body);
            color: var(--text-main);
            outline: none;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .chat-input:focus {
            border-color: var(--primary);
            background-color: var(--bg-card);
        }
        .chat-send-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0 24px;
            border-radius: var(--radius-xl);
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .chat-send-btn:hover {
            background-color: var(--primary-hover);
        }
    </style>
</head>

<body class="<?php echo $theme_preference == 'dark' ? 'dark-mode' : ''; ?>">

    <div class="layout-container">

        <div class="left-col">
            <?php include 'components/sidebar.php'; ?>
        </div>

        <div class="mid-col">
            <div class="header">Messages 💬</div>

            <div class="messaging-container">
                <!-- Contacts Pane -->
                <div class="contacts-pane">
                    <div style="padding: 15px 20px; border-bottom: 1px solid var(--border-color); font-weight: 800; font-size: 16px; color: var(--text-muted);">
                        Meowers Chats 🐾
                    </div>
                    <div class="contacts-list">
                        <?php foreach ($contacts as $contact): ?>
                            <a href="messages.php?contact_id=<?php echo $contact['id']; ?>" class="contact-item <?php echo $active_contact_id == $contact['id'] ? 'active' : ''; ?>">
                                <img src="uploads/avatars/<?php echo $contact['profile_pic']; ?>" class="avatar" style="width: 40px; height: 40px; margin: 0;" onerror="this.src='uploads/avatars/default_avatar.png'">
                                <div>
                                    <h5 style="margin: 0; font-size: 14px; font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($contact['name']); ?></h5>
                                    <span style="font-size: 12px; color: var(--text-muted);">@<?php echo htmlspecialchars($contact['username']); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Chat Pane -->
                <div class="chat-pane">
                    <?php if ($active_contact): ?>
                        <!-- Header Chat -->
                        <div class="chat-header">
                            <img src="uploads/avatars/<?php echo $active_contact['profile_pic']; ?>" class="avatar" style="width: 38px; height: 38px; margin: 0;" onerror="this.src='uploads/avatars/default_avatar.png'">
                            <div>
                                <h5 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($active_contact['name']); ?></h5>
                                <span style="font-size: 12px; color: var(--text-muted);">Aktif</span>
                            </div>
                        </div>

                        <!-- History Chat -->
                        <div class="chat-history" id="chatHistory">
                            <?php if (empty($messages)): ?>
                                <div style="margin: auto; text-align: center; color: var(--text-muted);">
                                    <i class="bi bi-chat-dots" style="font-size: 36px; opacity: 0.6;"></i>
                                    <p style="margin: 10px 0 0 0; font-size: 14px;">Belum ada pesan. Kirim meow-mu sekarang!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="bubble-msg <?php echo $msg['sender_id'] == $current_user_id ? 'sent' : 'received'; ?>">
                                        <?php echo htmlspecialchars($msg['message']); ?>
                                        <div style="font-size: 9px; opacity: 0.7; text-align: right; margin-top: 4px;">
                                            <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Input Area -->
                        <div class="chat-input-area">
                            <form method="POST" action="" class="chat-form" id="chatForm">
                                <input type="text" name="message_text" placeholder="Ketik meow ke <?php echo htmlspecialchars($active_contact['name']); ?>..." class="chat-input" required autocomplete="off" autofocus>
                                <button type="submit" name="send_message" class="chat-send-btn">Kirim</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- No Active Chat -->
                        <div style="margin: auto; text-align: center; color: var(--text-muted); padding: 40px;">
                            <i class="bi bi-chat-quote" style="font-size: 64px; display: block; margin-bottom: 20px; opacity: 0.6; color: var(--primary);"></i>
                            <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--text-main);">Cat-to-Cat Messages 🐾</h3>
                            <p style="margin: 8px 0 0 0; font-size: 14px;">Pilih salah satu Meower di panel sebelah kiri untuk memulai percakapan pribadi!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script>
        const activeContactId = <?php echo $active_contact_id ? $active_contact_id : 'null'; ?>;
        const currentUserId = <?php echo $current_user_id; ?>;

        function loadMessages() {
            if (!activeContactId) return;
            
            fetch('api_messages.php?action=get_messages&contact_id=' + activeContactId)
                .then(res => res.json())
                .then(messages => {
                    const chatHistory = document.getElementById('chatHistory');
                    if (!chatHistory) return;
                    
                    // Check if user is scrolled to bottom (or close to it)
                    const isAtBottom = chatHistory.scrollHeight - chatHistory.clientHeight <= chatHistory.scrollTop + 60;
                    
                    let html = '';
                    if (messages.length === 0) {
                        html = `<div style="margin: auto; text-align: center; color: var(--text-muted);">
                                    <i class="bi bi-chat-dots" style="font-size: 36px; opacity: 0.6;"></i>
                                    <p style="margin: 10px 0 0 0; font-size: 14px;">Belum ada pesan. Kirim meow-mu sekarang!</p>
                                </div>`;
                    } else {
                        messages.forEach(msg => {
                            const isSent = (msg.sender_id == currentUserId);
                            html += `<div class="bubble-msg ${isSent ? 'sent' : 'received'}">
                                        ${escapeHtml(msg.message)}
                                        <div style="font-size: 9px; opacity: 0.7; text-align: right; margin-top: 4px;">
                                            ${msg.time_str}
                                        </div>
                                    </div>`;
                        });
                    }
                    
                    chatHistory.innerHTML = html;
                    
                    // Always force scroll down on initial load or if user is already at bottom
                    if (isAtBottom || !chatHistory.dataset.loaded) {
                        chatHistory.scrollTop = chatHistory.scrollHeight;
                        chatHistory.dataset.loaded = "true";
                    }
                });
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Initial load
        loadMessages();

        // Polling interval (1.5 seconds)
        if (activeContactId) {
            setInterval(loadMessages, 1500);
        }

        // AJAX submit intercept
        const chatForm = document.getElementById('chatForm');
        if (chatForm) {
            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const input = chatForm.querySelector('input[name="message_text"]');
                const text = input.value.trim();
                if (!text) return;
                
                input.disabled = true;
                
                const formData = new FormData();
                formData.append('contact_id', activeContactId);
                formData.append('message_text', text);
                
                fetch('api_messages.php?action=send_message', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    input.disabled = false;
                    if (data.status === 'success') {
                        input.value = '';
                        input.focus();
                        loadMessages(); // Refresh chat immediately
                    }
                })
                .catch(err => {
                    input.disabled = false;
                    console.error(err);
                });
            });
        }
    </script>

</body>

</html>

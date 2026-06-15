<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once 'classes/Database.php';
$db = new Database();
$conn = $db->getConnection();
$current_user_id = $_SESSION['user_id'];

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'get_messages') {
    $contact_id = isset($_GET['contact_id']) ? (int)$_GET['contact_id'] : 0;
    if (!$contact_id) {
        echo json_encode([]);
        exit;
    }
    
    $query = "SELECT * FROM messages 
              WHERE (sender_id = '$current_user_id' AND receiver_id = '$contact_id') 
                 OR (sender_id = '$contact_id' AND receiver_id = '$current_user_id') 
              ORDER BY created_at ASC";
    $result = $conn->query($query);
    $messages = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['time_str'] = date('H:i', strtotime($row['created_at']));
            $messages[] = $row;
        }
    }
    echo json_encode($messages);
    exit;
}

if ($action == 'send_message') {
    $contact_id = isset($_POST['contact_id']) ? (int)$_POST['contact_id'] : 0;
    $msg_content = isset($_POST['message_text']) ? trim($_POST['message_text']) : '';
    
    if (!$contact_id || empty($msg_content)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        exit;
    }
    
    $msg_content = $conn->real_escape_string($msg_content);
    
    // Save user message
    $conn->query("INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$current_user_id', '$contact_id', '$msg_content')");
    
    echo json_encode(['status' => 'success']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);

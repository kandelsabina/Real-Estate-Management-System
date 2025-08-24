<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';
$property_id = intval($_GET['property_id'] ?? 0);

if (!$property_id) {
    die("Invalid property.");
}

// Fetch property seller id
$stmt = $conn->prepare("SELECT seller_id, title FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) {
    die("Property not found.");
}
$property = $res->fetch_assoc();
$seller_id = $property['seller_id'];
$property_title = $property['title'];

// Determine chat partner
if ($role === 'buyer') {
    $buyer_id = $user_id;
    $chat_partner_id = $seller_id;
} elseif ($role === 'seller') {
    $seller_id = $user_id;
    // Seller must provide buyer_id to chat with
    $buyer_id = intval($_GET['buyer_id'] ?? 0);
    if (!$buyer_id) {
        die("Buyer ID missing for chat.");
    }
    $chat_partner_id = $buyer_id;
} else {
    die("Unauthorized role.");
}

// Compose chat file path (ensure 'chats' directory exists and writable)
$chat_dir = __DIR__ . "/chats";
if (!is_dir($chat_dir)) {
    mkdir($chat_dir, 0777, true);
}
$chat_file = "$chat_dir/property_{$property_id}_buyer_{$buyer_id}_seller_{$seller_id}.json";

// Load existing messages
$messages = [];
if (file_exists($chat_file)) {
    $json = file_get_contents($chat_file);
    $messages = json_decode($json, true);
    if (!is_array($messages)) $messages = [];
}

// Handle new message send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text'])) {
    $message_text = trim($_POST['message_text']);
    if ($message_text !== '') {
        $new_message = [
            'sender_id' => $user_id,
            'message_text' => $message_text,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $messages[] = $new_message;
        file_put_contents($chat_file, json_encode($messages, JSON_PRETTY_PRINT));
        // Redirect to avoid form resubmission
        $redirectUrl = "chat.php?property_id={$property_id}";
        if ($role === 'seller') {
            $redirectUrl .= "&buyer_id={$buyer_id}";
        }
        header("Location: $redirectUrl");
        exit();
    }
}

function getUserName($id, $conn) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        return htmlspecialchars($row['username']);
    }
    return "User #$id";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Chat about: <?= htmlspecialchars($property_title) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
.chat-container {
    max-width: 700px;
    margin: 20px auto;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    height: 500px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}
.message {
    padding: 10px 15px;
    border-radius: 20px;
    margin-bottom: 10px;
    max-width: 75%;
    word-wrap: break-word;
}
.message.sent {
    background-color: #0d6efd;
    color: white;
    align-self: flex-end;
}
.message.received {
    background-color: #e9ecef;
    color: black;
    align-self: flex-start;
}
.form-chat {
    margin-top: auto;
}
</style>
</head>
<body>
<div class="container">
    <h3 class="my-4">Chat about: <?= htmlspecialchars($property_title) ?></h3>
    <div class="chat-container" id="chat-box">
        <?php foreach ($messages as $msg): 
            $isSent = $msg['sender_id'] == $user_id;
            $senderName = getUserName($msg['sender_id'], $conn);
        ?>
        <div class="message <?= $isSent ? 'sent' : 'received' ?>">
            <small><strong><?= $senderName ?></strong> @ <?= $msg['created_at'] ?></small><br/>
            <?= nl2br(htmlspecialchars($msg['message_text'])) ?>
        </div>
        <?php endforeach; ?>
    </div>
    <form method="POST" class="form-chat mt-3">
        <textarea name="message_text" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
        <button type="submit" class="btn btn-primary mt-2">Send</button>
    </form>
    <a href="property_detail.php?id=<?= $property_id ?>" class="btn btn-secondary mt-3">Back to Property</a>
</div>

<script>
    const chatBox = document.getElementById('chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;
</script>
</body>
</html>

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $property_id = intval($_POST['property_id']);
    $preferred_date = $_POST['preferred_date'];
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO visit_requests (property_id, user_id, preferred_date, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $property_id, $user_id, $preferred_date, $message);
    $stmt->execute();

    $email_stmt = $conn->prepare("
        SELECT u.email, u.username, p.title, p.location 
        FROM properties p 
        JOIN users u ON p.seller_id = u.id 
        WHERE p.id = ?
    ");
    $email_stmt->bind_param("i", $property_id);
    $email_stmt->execute();
    $result = $email_stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $to = $row['email'];
        $subject = "New Visit Request for: " . $row['title'];
        $body = "Hello " . $row['username'] . ",\n\n";
        $body .= "A buyer has requested a visit for your property: " . $row['title'] . "\n";
        $body .= "Location: " . $row['location'] . "\n";
        $body .= "Preferred Date: $preferred_date\n";
        $body .= "Message: " . ($message ?: 'No message provided') . "\n\n";
        $body .= "Please log in to your seller dashboard to respond.\n\n";
        $headers = "From: kandelsabina9@gmail.com";
        mail($to, $subject, $body, $headers);
    }

    echo "<script>alert('Visit request submitted!'); window.location.href='property_details.php?id=$property_id';</script>";
}
?>

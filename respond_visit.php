<?php
session_start();
include 'db.php';

if ($_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id']);
$action = $_GET['action'];

$responses = [
    'accept' => ['accepted', 'Visit accepted by seller'],
    'reject' => ['rejected', 'Seller is unavailable on requested date'],
    'reschedule' => ['reschedule_requested', 'Seller has requested rescheduling']
];

if (array_key_exists($action, $responses)) {
    [$status, $response] = $responses[$action];

    $stmt = $conn->prepare("UPDATE visit_requests SET status = ?, seller_response = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $response, $id);
    $stmt->execute();

    echo "<script>alert('Visit request $status.'); window.location.href='seller_visit_request.php';</script>";
}
?>
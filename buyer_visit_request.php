<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT vr.*, p.title AS property_title, p.location
    FROM visit_requests vr
    JOIN properties p ON vr.property_id = p.id
    WHERE vr.user_id = ?
    ORDER BY vr.created_at DESC
");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Visit Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">My Visit Requests</h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
        <tr>
            <th>Property</th>
            <th>Location</th>
            <th>Requested Date</th>
            <th>Message</th>
            <th>Status</th>
            <th>Seller Response</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
    <td>
        <a href="property_details.php?id=<?= $row['property_id'] ?>" class="btn btn-link p-0">
            <?= htmlspecialchars($row['property_title']) ?>
        </a>
    </td>
    <td><?= htmlspecialchars($row['location']) ?></td>
    <td><?= htmlspecialchars($row['preferred_date']) ?></td>
    <td><?= htmlspecialchars($row['message']) ?></td>
    <td>
        <span class="badge bg-<?= 
            $row['status'] === 'accepted' ? 'success' : (
            $row['status'] === 'rejected' ? 'danger' : (
            $row['status'] === 'reschedule_requested' ? 'warning' : 'secondary')) ?>">
            <?= ucfirst($row['status']) ?>
        </span>
    </td>
    <td><?= $row['seller_response'] ? htmlspecialchars($row['seller_response']) : '—' ?></td>
</tr>

        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>

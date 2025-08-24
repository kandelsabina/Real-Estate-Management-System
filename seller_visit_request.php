<?php
session_start();
include 'db.php';

if ($_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT vr.*, 
           u.username AS buyer_name, 
           u.contact_number, 
           p.title AS property_title, 
           p.id AS property_id
    FROM visit_requests vr
    JOIN properties p ON vr.property_id = p.id
    JOIN users u ON vr.user_id = u.id
    WHERE p.seller_id = ?
    ORDER BY vr.created_at DESC
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h3>Your Visit Requests</h3>
<table border="1" cellpadding="8">
<tr>
    <th>Buyer</th>
    <th>Contact</th>
    <th>Property</th>
    <th>Date</th>
    <th>Message</th>
    <th>Status</th>
    <th>Seller Response</th>
    <th>Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['buyer_name']) ?></td>
    <td>
        <?php if (!empty($row['contact_number'])): ?>
            <a href="tel:<?= htmlspecialchars($row['contact_number']) ?>">
                📞 <?= htmlspecialchars($row['contact_number']) ?>
            </a>
        <?php else: ?>
            <span class="text-muted">N/A</span>
        <?php endif; ?>
    </td>
    <td>
        <a href="manage_property.php?id=<?= $row['property_id'] ?>">
            <?= htmlspecialchars($row['property_title']) ?>
        </a>
    </td>
    <td><?= htmlspecialchars($row['preferred_date']) ?></td>
    <td><?= htmlspecialchars($row['message']) ?></td>
    <td><?= ucfirst($row['status']) ?></td>
    <td><?= htmlspecialchars($row['seller_response']) ?></td>
    <td>
        <?php if ($row['status'] === 'pending'): ?>
            <a href="respond_visit.php?action=accept&id=<?= $row['id'] ?>">✅ Accept</a> |
            <a href="respond_visit.php?action=reject&id=<?= $row['id'] ?>">❌ Reject</a> |
            <a href="respond_visit.php?action=reschedule&id=<?= $row['id'] ?>">🕒 Reschedule</a>
        <?php else: ?>
            <span class="text-muted">Done</span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['approve_id'])) {
    $approve_id = intval($_GET['approve_id']);
    $stmt = $conn->prepare("UPDATE users SET status = 'approved', is_verified = 1 WHERE id = ? AND role = 'seller'");
    $stmt->bind_param("i", $approve_id);
    $stmt->execute();
    header("Location: approve_sellers.php");
    exit();
}
$result = $conn->query("SELECT id, username, email FROM users WHERE role = 'seller' AND status = 'pending'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Approve Sellers | Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 800px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
            font-weight: 500;
        }
        .btn-approve:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Pending Seller Approvals</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered card p-3">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Seller Name</th>
                    <th>Email</th>
                    <th>Approve</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $count++ ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <a href="?approve_id=<?= $row['id'] ?>" class="btn btn-sm btn-approve">Approve</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info text-center">No sellers awaiting approval.</div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>

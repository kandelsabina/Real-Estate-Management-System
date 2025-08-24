<?php
session_start();
include 'db.php';

// Only admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];

   
    $del_user = $conn->prepare("DELETE FROM users WHERE id = ?");
    $del_user->bind_param("i", $user_id);
    $del_user->execute();

    header("Location: manage_users.php?deleted=1");
    exit();
}

// Fetch all users
$users = $conn->query("SELECT * FROM users WHERE role='seller' OR role='buyer' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="mb-4">Manage Users</h3>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">User deleted successfully.</div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Verified</th>
                <th>Joined</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users->num_rows > 0): ?>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= ucfirst($row['role']) ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td><?= $row['is_verified'] ? 'Yes' : 'No' ?></td>
                        <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                        <td>
                            <td>
    <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user and all associated data?')">Delete</a>
</td>

                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>

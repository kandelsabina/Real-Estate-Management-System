<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "User not found.";
    exit();
}
$user = $result->fetch_assoc();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $contact = trim($_POST['contact_number']);
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;
    $new_password = trim($_POST['new_password']);

    // Validation
    if (empty($username)) $errors[] = "Username is required.";
    if (!in_array($role, ['buyer', 'seller'])) $errors[] = "Invalid role.";
    if (!in_array($status, ['approved', 'pending'])) $errors[] = "Invalid status.";
    if (!preg_match('/^[0-9]{7,15}$/', $contact)) $errors[] = "Invalid contact number.";
    if (!empty($new_password) && strlen($new_password) < 6) $errors[] = "Password must be at least 6 characters.";

    if (empty($errors)) {
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET username = ?, role = ?, status = ?, is_verified = ?, contact_number = ?, password = ? WHERE id = ?");
            $update->bind_param("ssssssi", $username, $role, $status, $is_verified, $contact, $hashed, $user_id);
        } else {
            $update = $conn->prepare("UPDATE users SET username = ?, role = ?, status = ?, is_verified = ?, contact_number = ? WHERE id = ?");
            $update->bind_param("sssssi", $username, $role, $status, $is_verified, $contact, $user_id);
        }

        $update->execute();

        if ($update->affected_rows >= 0) {
            $success = true;

            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = "Failed to update user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; padding: 30px; }
        .form-container { max-width: 650px; margin: auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="form-container">
    <h3 class="mb-4">Edit User - ID <?= $user['id'] ?></h3>

    <?php if ($success): ?>
        <div class="alert alert-success">User updated successfully.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?>
                <div><?= $e ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>User ID</label>
            <input type="text" class="form-control" value="<?= $user['id'] ?>" readonly>
        </div>

        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label>Current Password (Hashed)</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['password']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label>New Password (leave blank to keep existing)</label>
            <input type="password" name="new_password" class="form-control" placeholder="Enter new password (min 6 chars)">
        </div>

        <div class="mb-3">
            <label>OTP Code</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['otp_code']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label>Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($user['contact_number']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Role</label>
            <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" readonly>
            <input type="hidden" name="role" value="<?= $user['role'] ?>">
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-select" required>
                <option value="approved" <?= $user['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="pending" <?= $user['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
            </select>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_verified" class="form-check-input" id="verifyCheck" <?= $user['is_verified'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="verifyCheck">Verified</label>
        </div>

        <div class="mb-3">
            <label>Created At</label>
            <input type="text" class="form-control" value="<?= $user['created_at'] ?>" readonly>
        </div>

        <button type="submit" class="btn btn-success">Update User</button>
        <a href="manage_users.php" class="btn btn-secondary">Back</a>
    </form>
</div>

</body>
</html>

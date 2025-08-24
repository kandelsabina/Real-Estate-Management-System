<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$role = $_SESSION['role'] ?? '';
$username_session = $_SESSION['username'] ?? '';

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit();
}

$success_message = "";
$error_message = "";

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_contact = trim($_POST['contact_number']);

    // Server-side validation
    if (empty($new_username) || empty($new_contact)) {
        $error_message = "Please fill in all required fields.";
    } elseif (!preg_match("/^[a-zA-Z0-9 ]+$/", $new_username)) {
        $error_message = "Username can only contain letters, numbers, and spaces.";
    } elseif (!preg_match("/^\d{10}$/", $new_contact)) {
        $error_message = "Contact number must be exactly 10 digits.";
    } else {
        $update = $conn->prepare("UPDATE users SET username = ?, contact_number = ? WHERE email = ?");
        $update->bind_param("sss", $new_username, $new_contact, $email);

        if ($update->execute()) {
            $_SESSION['username'] = $new_username;
            $success_message = "Profile updated successfully!";
            $user['username'] = $new_username;
            $user['contact_number'] = $new_contact;
        } else {
            $error_message = "Update failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .edit-form {
            max-width: 550px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .edit-form h3 {
            text-align: center;
            color: #0d6efd;
            margin-bottom: 25px;
        }

        .readonly-field {
            background-color: #e9ecef;
        }

        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="edit-form">
    <h3>Edit Profile</h3>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label class="form-label">Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" 
                   pattern="[A-Za-z0-9 ]{3,}" 
                   title="Only letters, numbers, and spaces allowed" 
                   value="<?= htmlspecialchars($_POST['username'] ?? $user['username'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contact Number <span class="text-danger">*</span></label>
            <input type="text" name="contact_number" class="form-control" 
                   pattern="\d{10}" maxlength="10" 
                   title="Must be exactly 10 digits"
                   value="<?= htmlspecialchars($_POST['contact_number'] ?? $user['contact_number'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control readonly-field" 
                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" class="form-control readonly-field" 
                   value="<?= htmlspecialchars($user['role'] ?? '') ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <input type="text" class="form-control readonly-field" 
                   value="<?= htmlspecialchars($user['status'] ?? '') ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Joined On</label>
            <input type="text" class="form-control readonly-field" 
                   value="<?= htmlspecialchars($user['created_at'] ?? '') ?>" readonly>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </div>
    </form>
</div>

</body>
</html>

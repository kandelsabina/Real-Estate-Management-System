<?php
session_start();
include 'db.php';

$errors = [];
$success = '';
$otpSent = false;

function sendOTP($email, $otp) {
    $subject = "Your OTP for Registration";
    $message = "Your OTP is: $otp";
    $headers = "From: kandelsabina111@gmail.com";
    return mail($email, $subject, $message, $headers); 
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $enteredOtp = $_POST['otp'];
    $pendingUser = $_SESSION['pending_user'] ?? null;

    if (!$pendingUser) {
        $errors[] = "Session expired. Please start again.";
    } elseif ($enteredOtp == $pendingUser['otp']) {
        $username = $pendingUser['username'];
        $email = $pendingUser['email'];
        $password = $pendingUser['password'];
        $contact = $pendingUser['contact'];
        $role = $pendingUser['role'];
        $otp = $pendingUser['otp'];

        $status = ($role === 'seller') ? 'pending' : 'approved';
        if ($role === 'buyer') {
    $status = 'approved';
    $is_verified = 1;
} else {
    $status = 'pending';
    $is_verified = 0;
}


        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, otp_code, is_verified, status, contact_number)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiis", $username, $email, $password, $role, $otp, $is_verified, $status, $contact);

        if ($stmt->execute()) {
            unset($_SESSION['pending_user']);
            $success = "Account created successfully. You can now <a href='login.php'>log in</a>.";
        } else {
            $errors[] = "Error while saving user: " . $stmt->error;
        }
    } else {
        $errors[] = "Incorrect OTP.";
        $otpSent = true;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $contact = trim($_POST['contact']);
    $role = $_POST['role'];

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = "Username can only contain letters, digits, and underscores.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (!preg_match('/^\d{10}$/', $contact)) $errors[] = "Contact number must be exactly 10 digits.";
    if (strlen($password) < 3) $errors[] = "Password must be at least 3 characters.";
    if (!in_array($role, ['buyer', 'seller'])) $errors[] = "Invalid role selected.";

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) $errors[] = "Email already registered.";

    if (empty($errors)) {
        $otp = rand(100000, 999999);
        $_SESSION['pending_user'] = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'contact' => $contact,
            'role' => $role,
            'otp' => $otp
        ];

        if (sendOTP($email, $otp)) {
            $success = "OTP sent to $email. Please enter it below.";
            $otpSent = true;
        } else {
            $errors[] = "Failed to send OTP.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register | Dream Homes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(to right, #dff9fb, #fef9f8); font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 500px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); background: white; }
        h2 { font-weight: 700; color: #130f40; }
        .form-label { font-weight: 600; }
        .btn { border-radius: 8px; font-weight: 600; }
        .alert { font-size: 0.95rem; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Create an Account</h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>$e</div>"; ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (!$otpSent): ?>
    <form method="post" class="card p-4 shadow-sm mb-4">
        <input type="hidden" name="register" value="1">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="">Select Role</option>
                <option value="buyer">Buyer</option>
                <option value="seller">Seller</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send OTP</button>
    </form>
    <?php endif; ?>

    <?php if ($otpSent): ?>
    <form method="post" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label">Enter OTP sent to your email</label>
            <input type="text" name="otp" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Verify & Register</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>

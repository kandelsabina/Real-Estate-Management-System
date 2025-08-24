<?php
session_start();
include 'db.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch user by email
    $stmt = $conn->prepare("SELECT id, username, email, password, role, is_verified, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ✅ Verify hashed password
        if (!password_verify($password, $user['password'])) {
            $errors[] = "Incorrect password.";
        } elseif ($user['role'] !== 'admin' && !$user['is_verified']) {
            $errors[] = "Please verify your email first.";
        } elseif ($user['role'] === 'seller' && $user['status'] !== 'approved') {
            $errors[] = "Seller account not approved yet.";
        } else {
            // ✅ Login success — set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            // ✅ Redirect based on role
            switch ($user['role']) {
                case 'admin':  header("Location: admin_dashboard.php"); break;
                case 'seller': header("Location: sellers_dashboard.php"); break;
                case 'buyer':  header("Location: buyer_dashboard.php"); break;
            }
            exit;
        }
    } else {
        $errors[] = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Dream Homes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #e3f2fd, #f1f2f6);
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 450px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #2c3e50;
            font-weight: 700;
        }
        .btn {
            border-radius: 8px;
            font-weight: 600;
        }
        .alert {
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4 text-center">Login to Dream Homes</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>$e</div>"; ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <div class="mb-3">
            <input type="email" name="email" placeholder="Email" class="form-control" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" placeholder="Password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="text-center mt-3">
        <small>Don't have an account? <a href="register.php">Register here</a></small>
    </div>
</div>
</body>
</html>

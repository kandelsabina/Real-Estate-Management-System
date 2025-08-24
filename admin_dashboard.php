<?php
session_start();
include 'db.php';

// Admin-only access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Admin';

// === Fetch Stats ===
$total_props     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM properties"))['total'] ?? 0;
$approved_props  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM properties WHERE status = 'approved'"))['total'] ?? 0;
$pending_props   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM properties WHERE status != 'approved'"))['total'] ?? 0;

$total_sellers   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'seller'"))['total'] ?? 0;
$total_buyers    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'buyer'"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
        }

        .sidebar {
            height: 100vh;
            width: 220px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }

        .sidebar h4 {
            text-align: center;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: #dcdcdc;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #495057;
            color: white;
        }

        .main-content {
            margin-left: 220px;
            padding: 20px;
        }

       .navbar-top {
    background-color: #0d6efd; /* changed to blue */
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white; /* make text white for contrast */
}


        .summary-section {
            margin-top: 30px;
        }

        .summary-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .summary-card {
            flex: 1;
            min-width: 240px;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            text-align: center;
        }

        .summary-card h4 {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 5px;
        }

        .summary-card p {
            margin: 0;
            font-weight: 500;
            color: #555;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4>Dream Homes</h4>
    <a href="admin_dashboard.php" class="active"> Dashboard</a>
    <a href="manage_users.php"> Manage Users</a>
    <a href="manage_properties.php"> Manage Properties</a>
    <a href="approve_properties.php"> Pending Properties</a>
    <a href="approve_sellers.php">Approve Sellers</a>
    <a href="logout.php"> Logout</a>
</div>

<!-- Main -->
<div class="main-content">
    <div class="navbar-top">
        <h5 class="mb-0">Admin Dashboard</h5>
        <span>Welcome, <strong><?= htmlspecialchars($username) ?></strong></span>
    </div>

    <!-- Summary Cards -->
    <div class="summary-section">
        <h4 class="mb-3">Platform Summary</h4>
        <div class="summary-cards">
            <div class="summary-card">
                <h4><?= $total_props ?></h4>
                <p>Total Properties</p>
            </div>
            <div class="summary-card">
                <h4><?= $approved_props ?></h4>
                <p>Approved Properties</p>
            </div>
            <div class="summary-card">
                <h4><?= $pending_props ?></h4>
                <p>Pending Properties</p>
            </div>
            <div class="summary-card">
                <h4><?= $total_sellers ?></h4>
                <p>Total Sellers</p>
            </div>
            <div class="summary-card">
                <h4><?= $total_buyers ?></h4>
                <p>Total Buyers</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

// Check seller approval
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id, status FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();

if (!$seller || $seller['status'] !== 'approved') {
    echo "<script>alert('Your seller account is not yet approved by the admin.'); window.location='logout.php';</script>";
    exit();
}

$user_id = $seller['id'];
$username = $_SESSION['username'];

// Fetch property stats
$total_query = "SELECT COUNT(*) as total FROM properties WHERE seller_id = $user_id";
$approved_query = "SELECT COUNT(*) as approved FROM properties WHERE seller_id = $user_id AND status = 'approved'";
$pending_query = "SELECT COUNT(*) as pending FROM properties WHERE seller_id = $user_id AND status != 'approved'";

$total_listings = mysqli_fetch_assoc(mysqli_query($conn, $total_query))['total'];
$approved_listings = mysqli_fetch_assoc(mysqli_query($conn, $approved_query))['approved'];
$pending_listings = mysqli_fetch_assoc(mysqli_query($conn, $pending_query))['pending'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Dashboard | Dream Homes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
        }

      .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 220px;
    background-color: navy; /* changed to navy */
    color: white;
    padding-top: 20px;
    overflow-y: auto;
}


        .sidebar h4 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .sidebar a {
            padding: 15px 20px;
            display: block;
            color: #dcdcdc;
            text-decoration: none;
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


        .dashboard-box {
            background: white;
            padding: 30px;
            margin-top: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        .dashboard-box h3 {
            font-weight: 600;
            margin-bottom: 30px;
            color: #0d6efd;
            text-align: center;
        }

        .summary-cards {
            display: flex;
            gap: 20px;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .summary-card {
            flex: 1;
            min-width: 220px;
            background-color: #e9f0fb;
            border-left: 5px solid #0d6efd;
            padding: 20px;
            border-radius: 10px;
        }

        .summary-card h4 {
            font-size: 28px;
            font-weight: bold;
            color: #0d6efd;
        }

        .summary-card p {
            margin: 0;
            font-size: 15px;
            color: #333;
        }

        .action-buttons {
            margin-top: 40px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-buttons a {
            padding: 12px 20px;
            background-color: #0d6efd;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
        }

        .action-buttons a:hover {
            background-color: #0b5ed7;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h4>Dream Homes</h4>
    <a href="seller_dashboard.php" class="active"> Dashboard</a>
    <a href="add_property.php"> Add Property</a>
    <a href="my_listings.php"> My Listings</a>
    <a href="edit_profile.php"> Edit Profile</a>
    <a href="seller_visit_request.php">Visit Request</a>
    <a href="seller_chats.php"> Chat</a> 
    <a href="logout.php"> Logout</a>
</div>
<div class="main-content">
    <div class="navbar-top">
        <h5 class="mb-0">Seller Dashboard</h5>
        <span>Welcome, <strong><?= htmlspecialchars($username) ?></strong></span>
    </div>

    <div class="dashboard-box">
        <h3>Listing Overview</h3>
        <div class="summary-cards">
            <div class="summary-card">
                <h4><?= $total_listings ?></h4>
                <p>Total Listings</p>
            </div>
            <div class="summary-card">
                <h4><?= $approved_listings ?></h4>
                <p>Approved Listings</p>
            </div>
            <div class="summary-card">
                <h4><?= $pending_listings ?></h4>
                <p>Pending Listings</p>
            </div>
        </div>   
    </div>
</div>

</body>
</html>

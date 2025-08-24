<?php
session_start();
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$user_email = $_SESSION['email'] ?? '';
$user = null;

if (!empty($user_email)) {
    $query = "SELECT * FROM users WHERE email = '$user_email'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
    }
}

$username = $user['username'] ?? 'Buyer';
$favorites_count = 0;

if (isset($user['id'])) {
    $user_id = $user['id'];
    $fav_query = "SELECT COUNT(*) AS total FROM favorites WHERE user_id = $user_id";
    $fav_result = mysqli_query($conn, $fav_query);

    if ($fav_result && mysqli_num_rows($fav_result) === 1) {
        $fav_row = mysqli_fetch_assoc($fav_result);
        $favorites_count = $fav_row['total'];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buyer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
       html, body {
    height: 100%;
    overflow-y: auto;
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background-color: #e0f0ff; /* changed from #f5f7fa */
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

        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            color: white;
        }

        .main-content {
            margin-left: 220px;
            padding: 20px;
            min-height: 100vh;
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

       .chatbox {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 400px; 
    background: #fff;
    border-radius: 10px;
    border: 1px solid #ddd;
    padding: 15px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    max-height: 70vh;
    overflow-y: auto;
    z-index: 999;
}

@media (max-width: 768px) {
    .chatbox {
        width: 90%;
        right: 5%;
        bottom: 10px;
    }
}


        .chatbox textarea {
            resize: none;
        }

      .chat-response {
    margin-top: 10px;
    background: #f8f9fa;
    padding: 15px;
    border-left: 4px solid #0d6efd;
    border-radius: 8px;
    font-size: 14.5px;
    color: #333;
    line-height: 1.6;
    white-space: normal; /* allow wrapping */
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.property-chat-card {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 12px 15px;
    margin-bottom: 10px;
    background-color: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.property-chat-card h6 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #0d6efd;
}

.property-chat-card p {
    margin: 3px 0;
}



        .dashboard-overview {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card-box {
            background: white;
            padding: 20px;
            flex: 1;
            min-width: 280px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .card-box h6 {
            font-weight: bold;
        }

        ul.list-group li {
            font-size: 15px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4>Dream Homes</h4>
    <a href="buyer_dashboard.php" class="active"> Dashboard</a>
    <a href="browse_properties.php"> Browse Properties</a>
    <a href="my_favorites.php"> My Favorites</a>
    <a href="buyer_visit_request.php" class="btn btn-outline-primary">My Visit Requests</a>

    <a href="logout.php"> Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="navbar-top">
        <h5 class="mb-0">Buyer Dashboard</h5>
        <span>Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></span>
    </div>

    <!-- Dashboard Overview -->
    <div class="mt-4 dashboard-overview">
        <div class="card-box">
            <h6 class="mb-3">Account Info</h6>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">Username: <?php echo htmlspecialchars($username); ?></li>
                <li class="list-group-item">Email: <?php echo htmlspecialchars($user_email); ?></li>
                <li class="list-group-item">Joined: <?php echo htmlspecialchars($user['created_at'] ?? 'N/A'); ?></li>
            </ul>
        </div>

   <div class="card-box">
    <h6 class="mb-3">Activity Summary</h6>
    <ul class="list-group list-group-flush">
        <li class="list-group-item">Favorites Saved: <?php echo $favorites_count; ?></li>
        <li class="list-group-item">Last Login: <?php echo date("d M, Y"); ?></li>
    </ul>
</div>


<!-- Chatbox -->
<!-- <div class="chatbox">
    <h6>Ask AI Assistant 🤖</h6>
    <form method="post" action="chatbot.php">
        <textarea name="prompt" rows="4" class="form-control" placeholder="Ask about prices, flats..."></textarea>
        <button type="submit" class="btn btn-primary mt-2 w-100">Send</button>
    </form>

    <?php if (isset($_SESSION['chat_response'])): ?>
        <div class="chat-response">
            <strong>AI says:</strong><br>
            <?php
            echo $_SESSION['chat_response'];
            unset($_SESSION['chat_response']);
            ?>
        </div>
    <?php endif; ?>
</div>
 -->
</body>
</html>

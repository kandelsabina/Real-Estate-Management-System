<?php
include 'db.php';
$sql = "SELECT * FROM properties WHERE status = 'approved' AND approved = 1 ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dream Homes - Find Your Property</title>
    <link rel="icon" href="assets/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f2f4f8;
            margin: 0;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .navbar-brand img {
            height: 40px;
            margin-right: 8px;
        }

        .hero {
            position: relative;
            background: url('images/images.jpeg') center/cover no-repeat;
            height: 420px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: blanchedalmond;
        }

        .hero::after {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            z-index: 1;
        }

        .hero .content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .hero h1 {
            font-size: 3.2rem;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.3rem;
            margin-top: 10px;
        }

        .property-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            background-color: #fff;
        }

        .property-card:hover {
            transform: translateY(-5px);
        }

        .property-card img {
            height: 220px;
            object-fit: cover;
            width: 100%;
        }

        .property-card .card-body {
            padding: 1rem;
        }

        .property-card h5 {
            font-weight: 600;
            color: #333;
        }

        .property-card .text-primary {
            font-weight: 600;
        }

        footer {
            background: #fff;
            padding: 20px 0;
            font-size: 14px;
            color: black;
            box-shadow: 0 -1px 5px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="images/download.jpeg" alt="Logo">
            <span class="fw-bold text-primary">Dream Homes</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#property">All Properties</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php">Signup</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="hero">
    <div class="content">
        <h1>Find Your Dream Property</h1>
        <p>Affordable. Verified. Trusted.</p>
    </div>
</div>

<!-- PROPERTY LISTINGS -->
<div class="container my-5" id="property">
    <h2 class="text-center mb-4 fw-bold">Featured Listings</h2>
    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()):
                $photos = explode(',', $row['photo_paths']);
                $firstPhoto = trim($photos[0] ?? 'assets/default.jpg');
            ?>
                <div class="col-md-4">
                    <div class="property-card">
                        <img src="<?php echo htmlspecialchars($firstPhoto); ?>" alt="Property Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($row['location']); ?></p>
                            <p class="text-primary">Rs. <?php echo number_format($row['price'], 2); ?></p>
                            <a href="property_details.php?id=<?php echo $row['id']; ?>" class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No properties found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- FOOTER -->
<footer class="text-center">
    &copy; <?php echo date("Y"); ?> Dream Homes. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

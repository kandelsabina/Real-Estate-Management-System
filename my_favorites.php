<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $remove_id = (int)$_POST['remove_id'];
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
    $stmt->bind_param("ii", $user_id, $remove_id);
    $stmt->execute();
}

// Fetch favorite properties
$query = "SELECT p.* FROM properties p 
          JOIN favorites f ON p.id = f.property_id 
          WHERE f.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$favorite_properties = [];
while ($row = $result->fetch_assoc()) {
    $favorite_properties[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Favorites | Dream Homes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .property-img { height: 220px; object-fit: cover; width: 100%; }
        .property-card { box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-radius: 10px; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center mb-4">My Favorite Properties</h2>

    <div class="row">
        <?php if (!empty($favorite_properties)): ?>
            <?php foreach ($favorite_properties as $p):
                $photos = explode(',', $p['photo_paths']);
                $photo = trim($photos[0] ?? 'assets/default.jpg');
            ?>
            <div class="col-md-4 mb-4">
                <div class="card property-card">
                    <img src="<?= htmlspecialchars($photo) ?>" class="property-img" alt="Property Image">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($p['title']) ?></h5>
                        <p><?= htmlspecialchars($p['location']) ?> | <?= htmlspecialchars($p['type']) ?></p>
                        <p>Rs. <?= number_format($p['price']) ?> | <?= $p['area'] ?> sq ft</p>
                        <p><?= $p['bedrooms'] ?> Beds | <?= $p['bathrooms'] ?> Baths</p>
                        <a href="property_details.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary w-100 mb-2">View Details</a>
                        <form method="post">
                            <input type="hidden" name="remove_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-danger w-100">❌ Remove from Favorites</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info text-center">You have no favorite properties yet.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle favorites
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    $property_id = (int)$_POST['property_id'];
    $check = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND property_id = ?");
    $check->bind_param("ii", $user_id, $property_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $remove = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
        $remove->bind_param("ii", $user_id, $property_id);
        $remove->execute();
    } else {
        $add = $conn->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
        $add->bind_param("ii", $user_id, $property_id);
        $add->execute();
    }
}

// Filters & Preferences
$locationFilter = $_GET['location'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$features = ['bedrooms', 'bathrooms', 'area', 'price', 'floor', 'total_floor', 'year_built', 'parking', 'road_size'];
$userPreferences = [];

foreach ($features as $f) {
    if (isset($_GET[$f]) && is_numeric($_GET[$f])) {
        $userPreferences[$f] = (float)$_GET[$f];
    }
}

// Fetch properties
$query = "SELECT * FROM properties WHERE approved = 1 AND availability_status = 'available'";
$params = [];
$types = '';

if (!empty($locationFilter)) {
    $query .= " AND location LIKE ?";
    $params[] = "%$locationFilter%";
    $types .= 's';
}
if (!empty($typeFilter)) {
    $query .= " AND type = ?";
    $params[] = $typeFilter;
    $types .= 's';
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

$favorites = [];
$fav_result = $conn->query("SELECT property_id FROM favorites WHERE user_id = $user_id");
while ($f = $fav_result->fetch_assoc()) {
    $favorites[] = $f['property_id'];
}


if (!empty($properties) && !empty($userPreferences)) {
    $min = $max = [];
    foreach ($features as $f) {
        $col = array_column($properties, $f);
        if (!empty($col)) {
            $min[$f] = min($col);
            $max[$f] = max($col);
        } else {
            $min[$f] = $max[$f] = 0;
        }
    }


    foreach ($properties as &$p) {
        foreach ($features as $f) {
            $range = $max[$f] - $min[$f];
            $p["norm_$f"] = (isset($p[$f]) && $range) ? ($p[$f] - $min[$f]) / $range : 0.5;
        }
    }

    
    $userVector = [];
    foreach ($features as $f) {
        $range = $max[$f] - $min[$f];
        $userVector["norm_$f"] = (isset($userPreferences[$f]) && $range)
            ? ($userPreferences[$f] - $min[$f]) / $range
            : 0.5;
    }

    // Similarity function
    function cosineSimilarity($a, $b) {
        $dot = $normA = $normB = 0;
        foreach ($a as $k => $v) {
            $dot += $v * $b[$k];
            $normA += $v * $v;
            $normB += $b[$k] * $b[$k];
        }
        return ($normA && $normB) ? $dot / (sqrt($normA) * sqrt($normB)) : 0;
    }

    foreach ($properties as &$p) {
        $vec = array_intersect_key($p, $userVector);
        $p['score'] = round(cosineSimilarity($userVector, $vec), 3);
    }

    usort($properties, fn($a, $b) => $b['score'] <=> $a['score']);
}
?>




<!DOCTYPE html>
<html>
<head>
    <title>Browse Properties | Dream Homes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .property-img { height: 220px; object-fit: cover; width: 100%; }
        .property-card { box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-radius: 10px; }
        .match-score { font-size: 0.9rem; color: #0d6efd; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center mb-4">Find Your Dream Property</h2>

    <!-- Filter Form -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-2"><input type="number" name="bedrooms" class="form-control" placeholder="Min Bedrooms" value="<?= htmlspecialchars($_GET['bedrooms'] ?? '') ?>"></div>
        <div class="col-md-2"><input type="number" name="bathrooms" class="form-control" placeholder="Min Bathrooms" value="<?= htmlspecialchars($_GET['bathrooms'] ?? '') ?>"></div>
        <div class="col-md-2"><input type="number" name="area" class="form-control" placeholder="Min Area" value="<?= htmlspecialchars($_GET['area'] ?? '') ?>"></div>
        <div class="col-md-2"><input type="number" name="price" class="form-control" placeholder="Max Price" value="<?= htmlspecialchars($_GET['price'] ?? '') ?>"></div>
        <div class="col-md-2"><input type="text" name="location" class="form-control" placeholder="Location" value="<?= htmlspecialchars($locationFilter) ?>"></div>
        <div class="col-md-2">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="Apartment" <?= $typeFilter === 'Apartment' ? 'selected' : '' ?>>Apartment</option>
                <option value="House" <?= $typeFilter === 'House' ? 'selected' : '' ?>>House</option>
                <option value="Flat" <?= $typeFilter === 'Flat' ? 'selected' : '' ?>>Flat</option>
            </select>
        </div>
        <div class="col-md-12 d-grid"><button class="btn btn-primary">Search</button></div>
    </form>

    <div class="row">
        <?php if (!empty($properties)): ?>
            <?php foreach ($properties as $p):
                $photos = explode(',', $p['photo_paths']);
                $photo = trim($photos[0] ?? 'assets/default.jpg');
                $isFav = in_array($p['id'], $favorites);
            ?>
            <div class="col-md-4 mb-4">
                <div class="card property-card">
                    <img src="<?= htmlspecialchars($photo) ?>" class="property-img" alt="Property">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($p['title']) ?></h5>
                        <p><?= htmlspecialchars($p['location']) ?> | <?= htmlspecialchars($p['type']) ?></p>
                        <p>Rs. <?= number_format($p['price']) ?> | <?= $p['area'] ?> sq ft</p>
                        <p><?= $p['bedrooms'] ?> Beds | <?= $p['bathrooms'] ?> Baths</p>
                        <?php if (isset($p['score'])): ?>
                            <p class="match-score">Match Score: <?= $p['score'] ?></p>
                        <?php endif; ?>
                        <a href="property_details.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary w-100 mb-2">View Details</a>
                        <form method="post">
                            <input type="hidden" name="property_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn <?= $isFav ? 'btn-danger' : 'btn-outline-success' ?> w-100">
                                <?= $isFav ? '❌ Remove from Favorites' : '❤️ Add to Favorites' ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info text-center">No properties found.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

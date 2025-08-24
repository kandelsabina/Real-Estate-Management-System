<?php
session_start();
include 'db.php';

// Seller only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch property owned by seller
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $property_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "<div style='padding:20px;color:red;'>Property not found or access denied.</div>";
    exit;
}

$property = $result->fetch_assoc();
$photos = explode(',', $property['photo_paths']);
$firstPhoto = trim($photos[0] ?? 'assets/default.jpg');

// Handle availability update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['new_status'])) {
    $new_status = $_POST['new_status'];
    if (in_array($new_status, ['available', 'sold', 'taken'])) {
        $update = $conn->prepare("UPDATE properties SET availability_status = ? WHERE id = ? AND seller_id = ?");
        $update->bind_param("sii", $new_status, $property_id, $seller_id);
        $update->execute();
        header("Location: manage_property.php?id=" . $property_id);
        exit();
    }
}

// Handle property detail update
$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_property'])) {
    $title = trim($_POST['title']);
    $location = trim($_POST['location']);
    $type = trim($_POST['type']);
    $price = floatval($_POST['price']);
    $bedrooms = intval($_POST['bedrooms']);
    $bathrooms = intval($_POST['bathrooms']);
    $area = intval($_POST['area']);
    $description = trim($_POST['description']);

    if (empty($title) || empty($location) || empty($type) || $price <= 0 || $bedrooms < 0 || $bathrooms < 0 || $area <= 0) {
        $errors[] = "All fields must be filled correctly.";
    }

    if (empty($errors)) {
        $update = $conn->prepare("
            UPDATE properties 
            SET title = ?, location = ?, type = ?, price = ?, bedrooms = ?, bathrooms = ?, area = ?, description = ? 
            WHERE id = ? AND seller_id = ?
        ");
        $update->bind_param("sssdiiisii", $title, $location, $type, $price, $bedrooms, $bathrooms, $area, $description, $property_id, $seller_id);

        if ($update->execute()) {
            $success = "Property updated successfully!";
            $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND seller_id = ?");
            $stmt->bind_param("ii", $property_id, $seller_id);
            $stmt->execute();
            $property = $stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = "Failed to update. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Property | Dream Homes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .property-img { height: 350px; object-fit: cover; width: 100%; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3">Manage Property: <?= htmlspecialchars($property['title']) ?></h2>

    <div class="card p-4 mb-4">
        <img src="<?= htmlspecialchars($firstPhoto) ?>" class="property-img mb-3" alt="Property Image">
        <p><strong>Location:</strong> <?= htmlspecialchars($property['location']) ?></p>
        <p><strong>Type:</strong> <?= htmlspecialchars($property['type']) ?></p>
        <p><strong>Price:</strong> Rs. <?= number_format($property['price']) ?></p>
        <p><strong>Bedrooms:</strong> <?= (int)$property['bedrooms'] ?> |
           <strong>Bathrooms:</strong> <?= (int)$property['bathrooms'] ?> |
           <strong>Area:</strong> <?= (int)$property['area'] ?> sq ft</p>
        <p><strong>Status:</strong> <?= ucfirst($property['availability_status']) ?></p>

        <form method="post" class="mt-3">
            <label for="new_status" class="form-label">Change Availability:</label>
            <select name="new_status" class="form-select mb-3">
                <option value="available" <?= $property['availability_status'] === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="sold" <?= $property['availability_status'] === 'sold' ? 'selected' : '' ?>>Sold</option>
                <option value="taken" <?= $property['availability_status'] === 'taken' ? 'selected' : '' ?>>Already Taken</option>
            </select>
            <button type="submit" class="btn btn-primary">Update Status</button>
        </form>
    </div>

    <!-- Toggle Button -->
    <button class="btn btn-outline-secondary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#editFormCollapse" aria-expanded="false" aria-controls="editFormCollapse">
        ✏️ Edit Property Details
    </button>

    <!-- Collapsible Edit Form -->
    <div class="collapse <?= !empty($success) || !empty($errors) ? 'show' : '' ?>" id="editFormCollapse">
        <h4>Edit Property Details</h4>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" class="row g-3">
            <input type="hidden" name="update_property" value="1">

            <div class="col-md-6">
                <label for="title" class="form-label">Property Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($property['title']) ?>" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="location" class="form-label">Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($property['location']) ?>" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="type" class="form-label">Type</label>
                <input type="text" name="type" value="<?= htmlspecialchars($property['type']) ?>" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="price" class="form-label">Price (Rs)</label>
                <input type="number" name="price" value="<?= htmlspecialchars($property['price']) ?>" class="form-control" required min="1">
            </div>

            <div class="col-md-4">
                <label for="bedrooms" class="form-label">Bedrooms</label>
                <input type="number" name="bedrooms" value="<?= (int)$property['bedrooms'] ?>" class="form-control" required min="0">
            </div>

            <div class="col-md-4">
                <label for="bathrooms" class="form-label">Bathrooms</label>
                <input type="number" name="bathrooms" value="<?= (int)$property['bathrooms'] ?>" class="form-control" required min="0">
            </div>

            <div class="col-md-4">
                <label for="area" class="form-label">Area (sq ft)</label>
                <input type="number" name="area" value="<?= (int)$property['area'] ?>" class="form-control" required min="1">
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($property['description']) ?></textarea>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-success">Update Property</button>
            </div>
        </form>
    </div>

    <div class="mt-4">
        <a href="my_listings.php" class="btn btn-secondary">&larr; Back to My Listings</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

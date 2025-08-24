<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id, status FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || $user['status'] !== 'approved') {
    echo "<script>alert('Your seller account is not approved yet.'); window.location='logout.php';</script>";
    exit();
}

$seller_id = intval($user['id']);
$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $price = floatval($_POST['price']);
    $booking_type = trim($_POST['booking_type']);
    $type = trim($_POST['type']);
    $bedrooms = intval($_POST['bedrooms']);
    $bathrooms = intval($_POST['bathrooms']);
    $area = intval($_POST['area']);

    $photo_paths = [];
    $document_paths = [];
    $uploadOk = true;

    if (!empty($_FILES['photos']['name'][0])) {
        foreach ($_FILES['photos']['tmp_name'] as $i => $tmpName) {
            $fileName = time() . "_" . basename($_FILES['photos']['name'][$i]);
            $target = "uploads/photos/" . $fileName;
            if (move_uploaded_file($tmpName, $target)) {
                $photo_paths[] = $target;
            } else {
                $errors[] = "Failed to upload photo: " . htmlspecialchars($_FILES['photos']['name'][$i]);
                $uploadOk = false;
            }
        }
    }

    // Document uploads
    if (!empty($_FILES['documents']['name'][0])) {
        foreach ($_FILES['documents']['tmp_name'] as $i => $tmpName) {
            $fileName = time() . "_" . basename($_FILES['documents']['name'][$i]);
            $target = "uploads/docs/" . $fileName;
            if (move_uploaded_file($tmpName, $target)) {
                $document_paths[] = $target;
            } else {
                $errors[] = "Failed to upload document: " . htmlspecialchars($_FILES['documents']['name'][$i]);
                $uploadOk = false;
            }
        }
    }

    // Validate required fields
    if ($uploadOk && $title && $location && $booking_type && is_numeric($price)) {
        $photo_str = implode(',', $photo_paths);
        $doc_str = implode(',', $document_paths);

        $stmt = $conn->prepare("INSERT INTO properties (
            title, description, location, latitude, longitude, price, booking_type, type, bedrooms,
            bathrooms, area, photo_paths, document_paths, seller_id, status, approved,
            availability_status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0, 'available', NOW())");

        $stmt->bind_param(
            "sssdddssiiissi",
            $title,
            $description,
            $location,
            $latitude,
            $longitude,
            $price,
            $booking_type,
            $type,
            $bedrooms,
            $bathrooms,
            $area,
            $photo_str,
            $doc_str,
            $seller_id
        );

        if ($stmt->execute()) {
            $success = "Property submitted successfully and pending admin approval.";
        } else {
            $errors[] = "Database error: " . htmlspecialchars($stmt->error);
        }
    } elseif (empty($errors)) {
        $errors[] = "Missing required fields or invalid data.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Property | Seller</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f1f3f5;
            font-family: 'Segoe UI', sans-serif;
        }
        .form-container {
            max-width: 750px;
            background: #fff;
            padding: 30px;
            margin: 50px auto;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2 class="mb-4 text-center">Add New Property</h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>" . $e . "</div>"; ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="row mb-3">
            <div class="col"><input type="text" name="title" class="form-control" placeholder="Property Title" required></div>
            <div class="col"><input type="text" name="location" class="form-control" placeholder="Location" required></div>
        </div>

        <div class="mb-3">
            <textarea name="description" class="form-control" rows="3" placeholder="Property Description"></textarea>
        </div>

        <div class="row mb-3">
            <div class="col"><input type="text" name="latitude" class="form-control" placeholder="Latitude"></div>
            <div class="col"><input type="text" name="longitude" class="form-control" placeholder="Longitude"></div>
        </div>

        <div class="row mb-3">
            <div class="col"><input type="number" name="price" step="0.01" class="form-control" placeholder="Price (Rs.)" required></div>
            <div class="col">
                <select name="booking_type" class="form-control" required>
                    <option value="" disabled selected>Select Booking Type</option>
                    <option value="Rent">Rent</option>
                    <option value="Sale">Sale</option>
                    <option value="Lease">Lease</option>
                    <option value="Short-Term">Short-Term</option>
                </select>
            </div>
            <div class="col">
                <select name="type" class="form-control" required>
                    <option value="" disabled selected>Select Property Type</option>
                    <option value="house">House</option>
                    <option value="apartment">Apartment</option>
                    <option value="land">Land</option>
                    <option value="commercial">Commercial</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col"><input type="number" name="bedrooms" class="form-control" placeholder="Bedrooms"></div>
            <div class="col"><input type="number" name="bathrooms" class="form-control" placeholder="Bathrooms"></div>
            <div class="col"><input type="number" name="area" class="form-control" placeholder="Area (sq ft)"></div>
        </div>

        <div class="mb-3">
            <label>Upload Photos (multiple allowed)</label>
            <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
        </div>

        <div class="mb-3">
            <label>Upload Documents (PDF/DOC/DOCX)</label>
            <input type="file" name="documents[]" class="form-control" accept=".pdf,.doc,.docx" multiple>
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit Property</button>
    </form>
</div>
</body>
</html>

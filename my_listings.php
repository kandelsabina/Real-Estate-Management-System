<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM properties WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Listings | Dream Homes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .property-card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .property-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .approved { background-color: #d4edda; color: #155724; }
        .pending { background-color: #fff3cd; color: #856404; }
        .rejected { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4 text-center">My Property Listings</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()):
                $photos = explode(',', $row['photo_paths']);
                $firstPhoto = trim($photos[0] ?? 'assets/default.jpg');
                $status = $row['status'];
                $approved = $row['approved'] ? 'approved' : 'pending';
                $availability = ucfirst($row['availability_status']);
            ?>
            <div class="col-md-4 mb-4">
                <div class="card property-card">
                    <img src="<?= htmlspecialchars($firstPhoto) ?>" class="card-img-top property-img" alt="Property Image">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                        <p class="text-muted mb-1">Rs. <?= number_format($row['price']) ?> | <?= htmlspecialchars($row['location']) ?></p>
                        <span class="badge status-badge <?= $approved === 'approved' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'pending') ?>">
                            <?= ucfirst($status) ?>
                        </span>
                        <span class="badge bg-secondary ms-2"><?= $availability ?></span>

                        <div class="mt-3">
                            <a href="manage_property.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm w-100 mb-1">View</a>
                            <!-- Future options -->
                            <!-- <a href="edit_property.php?id=<?= $row['id'] ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">Edit</a> -->
                            <!-- <a href="delete_property.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm w-100">Delete</a> -->
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">You haven't listed any properties yet.</div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="sellers_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>

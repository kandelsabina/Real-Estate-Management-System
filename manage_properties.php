<?php
session_start();
include 'db.php';

// Admin access only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $property_id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    header("Location: manage_properties.php?deleted=1");
    exit();
}

// Fetch all properties with seller name
$sql = "
    SELECT p.*, u.username AS seller_name
    FROM properties p
    LEFT JOIN users u ON p.seller_id = u.id
    ORDER BY p.created_at DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Properties | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            margin-top: 40px;
        }

        .table thead th {
            background-color: #e9ecef;
        }

        .table th, .table td {
            vertical-align: middle;
            font-size: 14px;
        }

        .table-responsive {
            max-height: 80vh;
            overflow: auto;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .alert {
            max-width: 1000px;
            margin: 20px auto;
        }

        .truncate {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="mb-4">Manage All Properties</h3>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Property deleted successfully.</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>Lat</th>
                    <th>Lng</th>
                    <th>Price</th>
                    <th>Type</th>
                    <th>Bed</th>
                    <th>Bath</th>
                    <th>Area</th>
                    <th>Photos</th>
                    <th>Docs</th>
                    <th>Seller</th>
                    <th>Status</th>
                    <th>Approved</th>
                    <th>Availability</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td class="truncate" title="<?= $row['title'] ?>"><?= htmlspecialchars($row['title']) ?></td>
                            <td class="truncate" title="<?= $row['description'] ?>"><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td><?= $row['latitude'] ?></td>
                            <td><?= $row['longitude'] ?></td>
                            <td>Rs. <?= number_format($row['price']) ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td><?= $row['bedrooms'] ?></td>
                            <td><?= $row['bathrooms'] ?></td>
                            <td><?= $row['area'] ?> sqft</td>
                            <td class="truncate" title="<?= $row['photo_paths'] ?>"><?= basename(explode(',', $row['photo_paths'])[0]) ?></td>
                            <td class="truncate" title="<?= $row['document_paths'] ?>"><?= basename($row['document_paths']) ?></td>
                            <td><?= htmlspecialchars($row['seller_name']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= $row['approved'] ? 'Yes' : 'No' ?></td>
                            <td><?= htmlspecialchars($row['availability_status']) ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <a href="?delete=<?= $row['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this property?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="19" class="text-center">No properties found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

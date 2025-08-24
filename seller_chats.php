<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'] ?? 0;
if (!$seller_id) {
    echo "Invalid session. Please log in again.";
    exit();
}

$stmt = $conn->prepare("SELECT id, title, photo_paths, price, location FROM properties WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Seller Chats | Dream Homes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
    .carousel-item img {
        height: 150px;
        object-fit: cover;
        border-radius: 5px;
    }
    .property-card {
        margin-bottom: 40px;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 8px;
        background: #fff;
    }
    .buyers-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Buyer Chats</h2>
    <p>Click on a buyer to chat.</p>

    <?php if (empty($properties)): ?>
        <p>You have no properties listed yet.</p>
    <?php else: ?>
        <?php foreach ($properties as $property): 
            $photos = array_filter(array_map('trim', explode(',', $property['photo_paths'])));
            ?>
            <div class="property-card shadow-sm">
                <h4><?= htmlspecialchars($property['title']) ?></h4>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <?php if (!empty($photos)): ?>
                        <div id="carousel<?= $property['id'] ?>" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($photos as $index => $photo): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= htmlspecialchars($photo) ?>" class="d-block w-100" alt="Property Image">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $property['id'] ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $property['id'] ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 150px; border-radius: 5px;">
                                No Image
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <p><strong>Location:</strong> <?= htmlspecialchars($property['location']) ?></p>
                        <p><strong>Price:</strong> Rs. <?= number_format($property['price'], 2) ?></p>
                    </div>
                </div>

                <?php
              
                $stmt2 = $conn->prepare("
                    SELECT DISTINCT u.id, u.username 
                    FROM property_views pv
                    JOIN users u ON pv.user_id = u.id
                    WHERE pv.property_id = ?");
                $stmt2->bind_param("i", $property['id']);
                $stmt2->execute();
                $buyers = $stmt2->get_result();
                ?>

                <?php if ($buyers->num_rows === 0): ?>
                    <p><em>No buyers viewed this property yet.</em></p>
                <?php else: ?>
                    <h5>Buyers who viewed this property:</h5>
                    <ul class="list-group buyers-list mb-3">
                        <?php while ($buyer = $buyers->fetch_assoc()): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <?php if (!empty($photos[0])): ?>
                                        <a href="manage_property.php?id=<?= $property['id'] ?>">
                                            <img src="<?= htmlspecialchars($photos[0]) ?>" alt="Property Image" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">
                                        </a>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($buyer['username']) ?></span>
                                </div>
                                <a href="chat.php?property_id=<?= $property['id'] ?>&buyer_id=<?= $buyer['id'] ?>" class="btn btn-sm btn-primary">Chat</a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="seller_dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

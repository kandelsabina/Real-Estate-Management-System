<?php
session_start();
include 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Invalid property ID.";
    exit;
}

$property_id = intval($_GET['id']);

// Fetch property and seller contact
$stmt = $conn->prepare("
    SELECT p.*, u.contact_number 
    FROM properties p 
    JOIN users u ON p.seller_id = u.id 
    WHERE p.id = ? AND p.approved = 1 AND p.status = 'approved'
");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Property not found.";
    exit;
}

$property = $result->fetch_assoc();
$photos = explode(',', $property['photo_paths']);
$latitude = floatval($property['latitude']);
$longitude = floatval($property['longitude']);

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer') {
    $user_id = $_SESSION['user_id'];

    $check = $conn->prepare("SELECT id FROM property_views WHERE user_id = ? AND property_id = ?");
    $check->bind_param("ii", $user_id, $property_id);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows === 0) {
        $log = $conn->prepare("INSERT INTO property_views (user_id, property_id) VALUES (?, ?)");
        $log->bind_param("ii", $user_id, $property_id);
        $log->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($property['title']) ?> | Property Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .carousel-item img {
            height: 450px;
            object-fit: cover;
        }
        .map-container {
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">
    <a href="index.php" class="btn btn-secondary mb-3">&larr; Back to Listings</a>

    <h2><?= htmlspecialchars($property['title']) ?></h2>
    <p class="text-muted"><?= htmlspecialchars($property['location']) ?> | Rs. <?= number_format($property['price'], 2) ?></p>

    <!-- Image Carousel -->
    <?php if (!empty($photos[0])): ?>
    <div id="propertyCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php foreach ($photos as $index => $photo): ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <img src="<?= htmlspecialchars(trim($photo)) ?>" class="d-block w-100" alt="Property Image">
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>
    <?php endif; ?>

    <div class="card p-4 mb-4">
        <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($property['description'])) ?></p>
        <p><strong>Type:</strong> <?= htmlspecialchars($property['type']) ?></p>
        <p><strong>Bedrooms:</strong> <?= (int)$property['bedrooms'] ?> |
           <strong>Bathrooms:</strong> <?= (int)$property['bathrooms'] ?> |
           <strong>Area:</strong> <?= (int)$property['area'] ?> sq ft</p>
        <p><strong>Status:</strong> <?= htmlspecialchars($property['availability_status']) ?></p>
            <p><strong>Booking Type:</strong> <?= htmlspecialchars($property['booking_type']) ?></p>


     <?php if (!empty($property['contact_number'])): ?>
    <p><strong>Seller Contact:</strong> 
        <a href="tel:<?= htmlspecialchars($property['contact_number']) ?>" class="btn btn-outline-success btn-sm ms-2">
            📞 <?= htmlspecialchars($property['contact_number']) ?>
        </a>
    </p>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'buyer'): ?>
    <a href="chat.php?property_id=<?= $property_id ?>" class="btn btn-primary mb-3">
         Inquiry with Seller
    </a>
<?php endif; ?>

    </div>

    <h5>Location on Map</h5>
    <div id="map" class="map-container mb-5"></div>
</div>

<script>
  function initMap() {
    const location = { lat: <?= $latitude ?>, lng: <?= $longitude ?> };
    const map = new google.maps.Map(document.getElementById("map"), {
      center: location,
      zoom: 15,
    });
    new google.maps.Marker({
      position: location,
      map: map,
      title: <?= json_encode($property['title']) ?>
    });
  }
</script>

<script async
    src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap">
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($_SESSION['role'] === 'buyer'): ?>
<div class="card p-4 mb-4">
    <h5>Schedule a Visit</h5>
    <form action="schedule_visit.php" method="POST">
        <input type="hidden" name="property_id" value="<?= $property_id ?>">
   <div class="mb-3">
    <label for="preferred_date" class="form-label">Preferred Visit Date:</label>
    <input type="date" id="preferred_date" name="preferred_date" class="form-control" required>
</div>

<script>
    // Set today's date as the minimum
    const today = new Date().toISOString().split('T')[0];
    document.getElementById("preferred_date").setAttribute("min", today);
</script>

        <div class="mb-3">
            <label for="message" class="form-label">Message to Seller (optional):</label>
            <textarea name="message" class="form-control" rows="3" placeholder="E.g. I’m available after 5 PM..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Request Visit</button>
    </form>
</div>
<
<?php endif; ?>



</body>
</html>


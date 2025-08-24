<?php
session_start();
include 'db.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$features = ['bedrooms', 'bathrooms', 'area', 'price'];


if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'approve') {
    $property_id = intval($_GET['id']);

    $stmt = $conn->prepare("UPDATE properties SET approved = 1, status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();

    $prop_stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $prop_stmt->bind_param("i", $property_id);
    $prop_stmt->execute();
    $property = $prop_stmt->get_result()->fetch_assoc();

    if ($property) {
     
        $sql = "
            SELECT DISTINCT u.id AS user_id, u.email
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            JOIN users u ON pv.user_id = u.id
            WHERE u.email IS NOT NULL AND u.email != ''
        ";
        $result = $conn->query($sql);

        $user_vectors = [];

        while ($row = $result->fetch_assoc()) {
            $user_id = $row['user_id'];
            $email = $row['email'];

            $view_stmt = $conn->prepare("
                SELECT bedrooms, bathrooms, area, price
                FROM properties
                WHERE id IN (SELECT property_id FROM property_views WHERE user_id = ?)
            ");
            $view_stmt->bind_param("i", $user_id);
            $view_stmt->execute();
            $views = $view_stmt->get_result();

            $count = 0;
            $sum = array_fill_keys($features, 0);
            while ($v = $views->fetch_assoc()) {
                foreach ($features as $f) {
                    $sum[$f] += floatval($v[$f]);
                }
                $count++;
            }

            if ($count > 0) {
                $avg = [];
                foreach ($features as $f) {
                    $avg[$f] = $sum[$f] / $count;
                }

                
                $dot = 0;
                foreach ($features as $f) {
                    $dot += $avg[$f] * floatval($property[$f]);
                }

                $user_vectors[] = [
                    'email' => $email,
                    'score' => $dot
                ];
            }
        }
        usort($user_vectors, fn($a, $b) => $b['score'] <=> $a['score']);

        
        foreach ($user_vectors as $user) {
            $to = $user['email'];
            $subject = "New Property Matching Your Preferences!";
            $link = "http://localhost/realestate/property_details.php?id=" . $property['id'];
            $message = "Hi,\n\nA new property titled \"{$property['title']}\" in {$property['location']} has been approved that matches your past interest.\n\n👉 View it here: $link\n\nThanks,\nRealEstate Team";
            $headers = "From: kandelsabina111@gmail.com";

            mail($to, $subject, $message, $headers);
        }
    }

    header("Location: approve_properties.php");
    exit();
}

// Reject property
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'reject') {
    $property_id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE properties SET approved = 0, status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    header("Location: approve_properties.php");
    exit();
}

// Fetch pending properties
$result = $conn->query("SELECT * FROM properties WHERE status = 'pending'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Approve Properties | Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 1000px; }
        .card { box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-radius: 10px; }
        .property-img { width: 100%; height: 150px; object-fit: cover; border-radius: 5px; }
        .btn-approve { background-color: #28a745; color: white; }
        .btn-reject { background-color: #dc3545; color: white; }
        .file-link { margin-right: 10px; color: #007bff; display: inline-block; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Pending Property Approvals</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()):
            $photos = explode(',', $row['photo_paths']);
            $docs = explode(',', $row['document_paths']);
        ?>
        <div class="card mb-4 p-4">
            <div class="row">
                <div class="col-md-5">
                    <div class="row g-2">
                        <?php foreach ($photos as $photo): ?>
                            <div class="col-6">
                                <img src="<?= htmlspecialchars(trim($photo)) ?>" class="property-img" alt="Photo">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-7">
                    <h5><?= htmlspecialchars($row['title']) ?> (Rs. <?= number_format($row['price']) ?>)</h5>
                    <p class="text-muted"><?= htmlspecialchars($row['location']) ?></p>
                    <p><strong>Type:</strong> <?= $row['type'] ?> |
                       <strong>Bedrooms:</strong> <?= $row['bedrooms'] ?> |
                       <strong>Bathrooms:</strong> <?= $row['bathrooms'] ?> |
                       <strong>Area:</strong> <?= $row['area'] ?> sq ft</p>
                    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                    <p><strong>Documents:</strong><br>
                        <?php foreach ($docs as $doc): if (trim($doc)): ?>
                            <a href="<?= htmlspecialchars(trim($doc)) ?>" target="_blank" class="file-link"><?= basename($doc) ?></a>
                        <?php endif; endforeach; ?>
                    </p>
                    <div class="mt-3">
                        <a href="?action=approve&id=<?= $row['id'] ?>" class="btn btn-sm btn-approve">Approve</a>
                        <a href="?action=reject&id=<?= $row['id'] ?>" class="btn btn-sm btn-reject ms-2">Reject</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">No properties pending approval.</div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>

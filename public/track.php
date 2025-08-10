<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/header.php';

if (isset($_GET['query'])) {
    $query = $_GET['query'];
    $stmt = $pdo->prepare('SELECT s.*, u.full_name FROM shipments s LEFT JOIN users u ON s.user_id = u.user_id WHERE s.tracking_number = ? OR s.container_number = ? OR u.phone = ? OR u.full_name LIKE ?');
    $stmt->execute([$query, $query, $query, '%' . $query . '%']);
    $shipments = $stmt->fetchAll();

    if ($shipments) {
        echo '<table><tr><th>Tracking</th><th>Status</th><th>Last Updated</th><th>Source</th></tr>';
        foreach ($shipments as $shipment) {
            echo '<tr><td>' . htmlspecialchars($shipment['tracking_number']) . '</td><td>' . htmlspecialchars($shipment['status']) . '</td><td>' . htmlspecialchars($shipment['created_at']) . '</td><td>Database</td></tr>';
        }
        echo '</table>';
    } else {
        echo 'No shipments found.';
    }
}

include __DIR__ . '/../includes/footer.php';

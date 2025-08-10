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
        echo '<div class="table-container">';
        echo '<table class="shipments-table">';
        echo '<thead><tr><th>Tracking Number</th><th>Container Number</th><th>Customer</th><th>Status</th><th>Last Updated</th><th>Source</th></tr></thead>';
        echo '<tbody>';
        foreach ($shipments as $shipment) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($shipment['tracking_number']) . '</td>';
            echo '<td>' . htmlspecialchars($shipment['container_number'] ?? '-') . '</td>';
            echo '<td>' . htmlspecialchars($shipment['full_name'] ?? 'Unknown') . '</td>';
            echo '<td><span class="status-badge status-' . strtolower(str_replace(' ', '-', $shipment['status'])) . '">' . htmlspecialchars($shipment['status']) . '</span></td>';
            echo '<td>' . date('M j, Y H:i', strtotime($shipment['created_at'])) . '</td>';
            echo '<td>Database</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    } else {
        echo '<div class="no-data"><p>No shipments found.</p></div>';
    }
}

include __DIR__ . '/../includes/footer.php';

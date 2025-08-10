<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUser();
include __DIR__ . '/../includes/header.php';

$stmt = $pdo->prepare('SELECT * FROM shipments WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$shipments = $stmt->fetchAll();

if ($shipments) {
    echo '<table><tr><th>Tracking</th><th>Status</th><th>CBM</th><th>Cartons</th><th>Weight</th><th>GW</th><th>Amount</th></tr>';
    foreach ($shipments as $shipment) {
        echo '<tr><td>' . htmlspecialchars($shipment['tracking_number']) . '</td><td>' . htmlspecialchars($shipment['status']) . '</td><td>' . htmlspecialchars($shipment['cbm']) . '</td><td>' . htmlspecialchars($shipment['cartons']) . '</td><td>' . htmlspecialchars($shipment['weight']) . '</td><td>' . htmlspecialchars($shipment['gross_weight']) . '</td><td>' . htmlspecialchars($shipment['total_amount']) . '</td></tr>';
    }
    echo '</table>';
} else {
    echo 'No shipments found.';
}

include __DIR__ . '/../includes/footer.php';

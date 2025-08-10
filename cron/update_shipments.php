<?php
require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->prepare('SELECT * FROM shipments WHERE status != ?');
$stmt->execute(['Delivered']);
$shipments = $stmt->fetchAll();

foreach ($shipments as $shipment) {
    // TODO: Call scraper stubs 
    echo 'Processing shipment: ' . $shipment['tracking_number'] . PHP_EOL;
}

echo 'Cron job completed.' . PHP_EOL;

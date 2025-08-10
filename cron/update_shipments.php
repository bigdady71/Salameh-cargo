<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/scrapers/tracktrace.php';

echo "Starting shipment update process...\n";

try {
    // Get all shipments that are not delivered
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE status != ?');
    $stmt->execute(['Delivered']);
    $shipments = $stmt->fetchAll();

    $updated = 0;
    $errors = 0;

    foreach ($shipments as $shipment) {
        echo "Processing shipment: {$shipment['tracking_number']}\n";

        // Try to get status from tracktrace if container number exists
        if (!empty($shipment['container_number'])) {
            $result = fetchStatus($shipment['container_number']);

            if ($result) {
                try {
                    // Log the scrape result
                    $stmt = $pdo->prepare('INSERT INTO shipment_scrapes (shipment_id, source_site, status, status_raw) VALUES (?, ?, ?, ?)');
                    $stmt->execute([
                        $shipment['shipment_id'],
                        $result['source'],
                        $result['status'],
                        $result['status_raw'] ?? ''
                    ]);

                    // Update shipment status if it's different
                    if ($result['status'] !== $shipment['status']) {
                        $stmt = $pdo->prepare('UPDATE shipments SET status = ? WHERE shipment_id = ?');
                        $stmt->execute([$result['status'], $shipment['shipment_id']]);

                        echo "  Status updated: {$shipment['status']} -> {$result['status']}\n";
                        $updated++;
                    } else {
                        echo "  Status unchanged: {$shipment['status']}\n";
                    }
                } catch (PDOException $e) {
                    echo "  Database error: " . $e->getMessage() . "\n";
                    $errors++;
                }
            } else {
                echo "  No status found for container: {$shipment['container_number']}\n";
            }
        } else {
            echo "  No container number for tracking: {$shipment['tracking_number']}\n";
        }

        // Add a small delay to avoid overwhelming the server
        usleep(500000); // 0.5 seconds
    }

    echo "\nUpdate completed:\n";
    echo "- Shipments processed: " . count($shipments) . "\n";
    echo "- Status updates: $updated\n";
    echo "- Errors: $errors\n";
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Cron job completed successfully.\n";

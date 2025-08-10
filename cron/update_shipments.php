<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/refresh.php";

echo "Starting shipment update process...\n";

try {
    // Get all non-delivered shipments that need updating
    $stmt = $pdo->query('
        SELECT shipment_id, tracking_number 
        FROM shipments 
        WHERE status != "Delivered" 
        AND (
            updated_at IS NULL 
            OR TIMESTAMPDIFF(HOUR, updated_at, NOW()) >= 12
        )
    ');

    $shipments = $stmt->fetchAll();
    $updatedCount = 0;
    $errors = 0;

    foreach ($shipments as $shipment) {
        echo "Processing shipment ID: {$shipment['shipment_id']}\n";

        $identifiers = getShipmentIdentifiers($shipment['shipment_id']);

        if ($identifiers) {
            if (refreshShipmentStatus($shipment['shipment_id'], $identifiers)) {
                echo "  Status updated successfully\n";
                $updatedCount++;
            } else {
                echo "  No new status found\n";
            }
        } else {
            echo "  No tracking identifiers found\n";
            $errors++;
        }

        // Add a small delay to avoid overwhelming servers
        usleep(500000); // 0.5 seconds
    }

    // Log the cron run
    $stmt = $pdo->prepare('
            INSERT INTO logs (action_type, actor_id, details) 
            VALUES (:type, :actor_id, :details)
        ');

    $stmt->execute([
        'type' => 'cron_run',
        'actor_id' => 0,
        'details' => "Auto-update completed: $updatedCount shipment(s) updated, $errors error(s)"
    ]);

    echo "\nUpdate process completed:\n";
    echo "- Updated: $updatedCount shipments\n";
    echo "- Errors: $errors\n";
} catch (Exception $e) {
    // Log any errors
    $stmt = $pdo->prepare('
        INSERT INTO logs (action_type, actor_id, details) 
        VALUES (:type, :actor_id, :details)
    ');

    $stmt->execute([
        'type' => 'cron_error',
        'actor_id' => 0,
        'details' => 'Cron error: ' . $e->getMessage()
    ]);

    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Cron job completed successfully.\n";

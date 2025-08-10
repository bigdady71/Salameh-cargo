<?php

require_once __DIR__ . '/db.php';

/**
 * Refresh a shipment's status by trying different tracking sources
 * 
 * @param int $shipmentId The ID of the shipment to refresh
 * @param array $identifiers Array of identifiers (container_number, bl_number, etc.)
 * @return bool True if status was updated, false otherwise
 */
function refreshShipmentStatus(int $shipmentId, array $identifiers): bool
{
    global $pdo;

    // Define scrapers in priority order
    $scrapers = [
        'tracktrace' => __DIR__ . '/scrapers/tracktrace.php',
        'port_beirut' => __DIR__ . '/scrapers/port_beirut.php',
        'cma_cgm' => __DIR__ . '/scrapers/cma_cgm.php',
        'msc' => __DIR__ . '/scrapers/msc.php',
        'maersk' => __DIR__ . '/scrapers/maersk.php',
        'evergreen' => __DIR__ . '/scrapers/evergreen.php',
        'one' => __DIR__ . '/scrapers/one.php'
    ];

    try {
        // Get current shipment status
        $stmt = $pdo->prepare('SELECT status FROM shipments WHERE shipment_id = ?');
        $stmt->execute([$shipmentId]);
        $currentStatus = $stmt->fetchColumn();

        // If already delivered, skip update
        if ($currentStatus === 'Delivered') {
            return false;
        }

        // Try each scraper in order
        foreach ($scrapers as $scraper => $path) {
            if (!file_exists($path)) {
                continue;
            }

            require_once $path;
            $fetchStatus = 'fetchStatus'; // All scrapers must implement this function

            if (function_exists($fetchStatus)) {
                $newStatus = $fetchStatus($identifiers);

                if ($newStatus && $newStatus !== $currentStatus) {
                    // Begin transaction
                    $pdo->beginTransaction();

                    try {
                        // Update shipment status
                        $updateStmt = $pdo->prepare('
                            UPDATE shipments 
                            SET status = :status, updated_at = NOW() 
                            WHERE shipment_id = :id
                        ');

                        $updateStmt->execute([
                            'status' => $newStatus,
                            'id' => $shipmentId
                        ]);

                        // Log the scrape attempt
                        $scrapeStmt = $pdo->prepare('
                            INSERT INTO shipment_scrapes (
                                shipment_id, scraper, status, scrape_time
                            ) VALUES (
                                :shipment_id, :scraper, :status, NOW()
                            )
                        ');

                        $scrapeStmt->execute([
                            'shipment_id' => $shipmentId,
                            'scraper' => $scraper,
                            'status' => $newStatus
                        ]);

                        // Log the status change
                        $logStmt = $pdo->prepare('
                            INSERT INTO logs (
                                action_type, actor_id, details
                            ) VALUES (
                                :type, :actor_id, :details
                            )
                        ');

                        $logStmt->execute([
                            'type' => 'status_update',
                            'actor_id' => 0, // System action
                            'details' => "Shipment #$shipmentId status updated from \"$currentStatus\" to \"$newStatus\" via $scraper"
                        ]);

                        $pdo->commit();
                        return true;
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        throw $e;
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Log the error
        $stmt = $pdo->prepare('
            INSERT INTO logs (action_type, actor_id, details) 
            VALUES (:type, :actor_id, :details)
        ');

        $stmt->execute([
            'type' => 'scraper_error',
            'actor_id' => 0,
            'details' => 'Error refreshing shipment #' . $shipmentId . ': ' . $e->getMessage()
        ]);
    }

    return false;
}

/**
 * Get identifiers for a shipment
 * 
 * @param int $shipmentId Shipment ID
 * @return array|null Array of identifiers or null if not found
 */
function getShipmentIdentifiers(int $shipmentId): ?array
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT 
            container_number,
            bl_number,
            tracking_number,
            shipping_line,
            vessel_name
        FROM shipments 
        WHERE shipment_id = ?
    ');

    $stmt->execute([$shipmentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Check if a shipment needs refreshing
 * 
 * @param int $shipmentId Shipment ID
 * @param int $maxAge Maximum age in hours before refresh needed
 * @return bool True if refresh needed
 */
function needsRefresh(int $shipmentId, int $maxAge = 12): bool
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT 
            CASE 
                WHEN updated_at IS NULL THEN true
                WHEN TIMESTAMPDIFF(HOUR, updated_at, NOW()) > ? THEN true
                ELSE false
            END as needs_refresh,
            status
        FROM shipments 
        WHERE shipment_id = ?
    ');

    $stmt->execute([$maxAge, $shipmentId]);
    $result = $stmt->fetch();

    // Don't refresh if already delivered
    if ($result['status'] === 'Delivered') {
        return false;
    }

    return $result['needs_refresh'];
}

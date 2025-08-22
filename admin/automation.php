<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Automation Controls';
include __DIR__ . '/../includes/admin-header.php';

// Ensure user is logged in as admin
requireAdmin();

// Check if admin has sufficient privileges for manual trigger
function canTriggerUpdate()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT role FROM admins WHERE admin_id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $role = $stmt->fetchColumn();
    return in_array($role, ['superadmin', 'manager']);
}

$success_message = '';
$error_message = '';

// Handle manual trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trigger_update'])) {
    if (!canTriggerUpdate()) {
        $error_message = 'Insufficient privileges to trigger manual update.';
    } else {
        try {
            // Execute the update script
            $output = [];
            $return_var = 0;
            exec('php ' . escapeshellarg(__DIR__ . '/../cron/update_shipments.php'), $output, $return_var);

            if ($return_var === 0) {
                $success_message = 'Manual update triggered successfully.';

                // Log the manual trigger
                $stmt = $pdo->prepare('
                    INSERT INTO logs (action_type, actor_id, details) 
                    VALUES (?, ?, ?)
                ');
                $stmt->execute([
                    'manual_update',
                    -$_SESSION['admin_id'],
                    'Manual update triggered by admin'
                ]);
            } else {
                $error_message = 'Error executing update script.';
                error_log('Error running update_shipments.php: ' . implode("\n", $output));
            }
        } catch (Exception $e) {
            $error_message = 'An error occurred while triggering the update.';
            error_log('Error in automation.php: ' . $e->getMessage());
        }
    }
}

// Get last cron run time and stats
$lastRun = $pdo->query('
    SELECT MAX(scrape_time) as last_run,
           COUNT(DISTINCT shipment_id) as shipments_count
    FROM shipment_scrapes
')->fetch();

// Get next scheduled run time (assuming daily cron at midnight)
$nextRun = date('Y-m-d H:i:s', strtotime('tomorrow midnight'));

// Get recent scrape events
$recentScrapes = $pdo->query('
    SELECT ss.source_site, ss.status, ss.scrape_time, 
           s.tracking_number, s.container_number
    FROM shipment_scrapes ss
    JOIN shipments s ON s.shipment_id = ss.shipment_id
    ORDER BY ss.scrape_time DESC
    LIMIT 10
')->fetchAll();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Automation Controls - Salameh Cargo</title>
    <style>
    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
    }

    .stat-label {
        color: #6c757d;
        margin-top: 5px;
    }

    .logs-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .logs-table th,
    .logs-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
    }

    .logs-table th {
        background: #f8f9fa;
        font-weight: bold;
    }

    .message {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .trigger-button {
        background: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    .trigger-button:hover {
        background: #0056b3;
    }

    .trigger-button:disabled {
        background: #6c757d;
        cursor: not-allowed;
    }

    .timestamp {
        color: #6c757d;
        font-size: 0.9em;
    }
    </style>
</head>

<body>
    <div class="admin-content">
        <?php if ($success_message): ?>
        <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">
                    <?php echo $lastRun['last_run'] ? date('M j, Y H:i', strtotime($lastRun['last_run'])) : 'Never'; ?>
                </div>
                <div class="stat-label">Last Update Run</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $nextRun ? date('M j, Y H:i', strtotime($nextRun)) : 'Unknown'; ?>
                </div>
                <div class="stat-label">Next Scheduled Run</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($lastRun['shipments_count'] ?? 0); ?></div>
                <div class="stat-label">Shipments Updated</div>
            </div>
        </div>

        <div class="card">
            <h2>Manual Update</h2>
            <form method="post">
                <button type="submit" name="trigger_update" class="trigger-button"
                    <?php echo canTriggerUpdate() ? '' : 'disabled'; ?>>
                    Trigger Manual Update
                </button>
                <?php if (!canTriggerUpdate()): ?>
                <p class="error">Note: Only superadmin and manager roles can trigger manual updates.</p>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h2>Recent Update Events</h2>
            <table class="logs-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Source</th>
                        <th>Shipment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentScrapes as $scrape): ?>
                    <tr>
                        <td class="timestamp">
                            <?php echo date('Y-m-d H:i:s', strtotime($scrape['scrape_time'])); ?>
                        </td>
                        <td><?php echo htmlspecialchars($scrape['source_site']); ?></td>
                        <td>
                            <?php
                                echo htmlspecialchars($scrape['container_number'] ?? $scrape['tracking_number']);
                                ?>
                        </td>
                        <td><?php echo htmlspecialchars($scrape['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentScrapes)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No recent updates found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
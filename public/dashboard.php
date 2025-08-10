<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUser();
include __DIR__ . '/../includes/header.php';

$stmt = $pdo->prepare('SELECT * FROM shipments WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$shipments = $stmt->fetchAll();
?>

<main>
    <div class="container">
        <h1>Your Shipments</h1>

        <?php if ($shipments): ?>
            <div class="table-container">
                <table class="shipments-table">
                    <thead>
                        <tr>
                            <th>Tracking Number</th>
                            <th>Container Number</th>
                            <th>Status</th>
                            <th>CBM</th>
                            <th>Cartons</th>
                            <th>Weight</th>
                            <th>GW</th>
                            <th>Amount</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $shipment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($shipment['tracking_number']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['container_number'] ?? '-'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $shipment['status'])); ?>">
                                        <?php echo htmlspecialchars($shipment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($shipment['cbm']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['cartons']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['weight']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['gross_weight']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['total_amount']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($shipment['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="summary">
                <p>Total shipments: <?php echo count($shipments); ?></p>
            </div>
        <?php else: ?>
            <div class="no-data">
                <p>No shipments found for your account.</p>
                <p>Contact our support team to add your shipments.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
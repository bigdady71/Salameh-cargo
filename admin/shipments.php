<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
include __DIR__ . '/../includes/header.php';

$message = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $shipment_id = $_POST['shipment_id'] ?? 0;
        $new_status = $_POST['new_status'] ?? '';

        if ($shipment_id && $new_status) {
            try {
                $stmt = $pdo->prepare('UPDATE shipments SET status = ? WHERE shipment_id = ?');
                $stmt->execute([$new_status, $shipment_id]);

                // Log the action
                logAction('status_update', -$_SESSION['admin_id'], $shipment_id, "Status changed to: $new_status");

                $message = 'Shipment status updated successfully.';
            } catch (PDOException $e) {
                $error = 'Error updating shipment: ' . $e->getMessage();
            }
        } else {
            $error = 'Invalid shipment ID or status.';
        }
    }
}

// Build query with filters
$where_conditions = [];
$params = [];

if (!empty($_GET['status'])) {
    $where_conditions[] = 's.status = ?';
    $params[] = $_GET['status'];
}

if (!empty($_GET['search'])) {
    $search = $_GET['search'];
    $where_conditions[] = '(s.tracking_number LIKE ? OR s.container_number LIKE ? OR u.full_name LIKE ? OR u.phone LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get shipments
$shipments = [];
try {
    $sql = "SELECT s.*, u.full_name, u.phone 
            FROM shipments s 
            LEFT JOIN users u ON s.user_id = u.user_id 
            $where_clause 
            ORDER BY s.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $shipments = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading shipments: ' . $e->getMessage();
}

// Get unique statuses for filter
$statuses = [];
try {
    $stmt = $pdo->prepare('SELECT DISTINCT status FROM shipments ORDER BY status');
    $stmt->execute();
    $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Ignore error for status filter
}
?>

<div class="container">
    <h1>Manage Shipments</h1>

    <?php if ($message): ?>
        <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filters">
        <form method="get" class="filter-form">
            <div class="form-group">
                <label for="search">Search:</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Tracking, Container, Name, Phone">
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo ($_GET['status'] ?? '') === $status ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="shipments.php" class="btn btn-secondary">Clear</a>
        </form>
    </div>

    <!-- Shipments Table -->
    <div class="table-container">
        <table class="shipments-table">
            <thead>
                <tr>
                    <th>Tracking Number</th>
                    <th>Container Number</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($shipments)): ?>
                    <tr>
                        <td colspan="6" class="no-data">No shipments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($shipments as $shipment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($shipment['tracking_number']); ?></td>
                            <td><?php echo htmlspecialchars($shipment['container_number'] ?? '-'); ?></td>
                            <td>
                                <?php echo htmlspecialchars($shipment['full_name'] ?? 'Unknown'); ?>
                                <?php if ($shipment['phone']): ?>
                                    <br><small><?php echo htmlspecialchars($shipment['phone']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $shipment['status'])); ?>">
                                    <?php echo htmlspecialchars($shipment['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y H:i', strtotime($shipment['created_at'])); ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="shipment_id" value="<?php echo $shipment['shipment_id']; ?>">
                                    <select name="new_status" class="status-select">
                                        <option value="">Change Status</option>
                                        <option value="En Route">En Route</option>
                                        <option value="In Transit">In Transit</option>
                                        <option value="Arrived at Port">Arrived at Port</option>
                                        <option value="Customs Clearance">Customs Clearance</option>
                                        <option value="Out for Delivery">Out for Delivery</option>
                                        <option value="Delivered">Delivered</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Booked">Booked</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-small">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="summary">
        <p>Total shipments: <?php echo count($shipments); ?></p>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
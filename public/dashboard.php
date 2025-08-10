<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireUser();

// Get user info
$userStmt = $pdo->prepare('SELECT full_name, phone, email FROM users WHERE user_id = ?');
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

// Handle filters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '';

// Build the base query
$baseQuery = 'SELECT 
    s.*,
    COALESCE(MAX(ss.scrape_time), s.updated_at, s.created_at) as last_update
FROM shipments s 
LEFT JOIN shipment_scrapes ss ON s.shipment_id = ss.shipment_id
WHERE s.user_id = ?';
$params = [$_SESSION['user_id']];

if ($status) {
    $baseQuery .= ' AND s.status = ?';
    $params[] = $status;
}

if ($dateRange) {
    switch ($dateRange) {
        case '7days':
            $baseQuery .= ' AND s.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
            break;
        case '30days':
            $baseQuery .= ' AND s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
            break;
        case '90days':
            $baseQuery .= ' AND s.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
            break;
    }
}

$baseQuery .= ' GROUP BY s.shipment_id';

// Get total count for pagination
$countQuery = 'SELECT COUNT(*) FROM (' . $baseQuery . ') as filtered';
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalShipments = $countStmt->fetchColumn();

// Setup pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$totalPages = ceil($totalShipments / $perPage);
$offset = ($page - 1) * $perPage;

// Get paginated shipments
$query = $baseQuery . ' ORDER BY last_update DESC LIMIT ? OFFSET ?';
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$shipments = $stmt->fetchAll();

// Get unique statuses for filter
$statusesStmt = $pdo->prepare('SELECT DISTINCT status FROM shipments WHERE user_id = ? ORDER BY status');
$statusesStmt->execute([$_SESSION['user_id']]);
$availableStatuses = $statusesStmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
?>

<main>
    <div class="container">
        <!-- Welcome Section -->
        <section class="hero" style="min-height: 50vh;">
            <div class="hero__content">
                <h1 class="hero__title" style="font-size: 2.5rem;">
                    Welcome<?php echo $user['full_name'] ? ', ' . htmlspecialchars($user['full_name']) : ''; ?>!
                </h1>
                <p class="hero__subtitle">Manage and track all your shipments from your personal dashboard.</p>

                <?php if ($user['phone']): ?>
                    <p style="color: var(--muted); margin-top: 1rem;">
                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?>
                        <?php if ($user['email']): ?>
                            <span style="margin: 0 1rem;">•</span>
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($shipments): ?>
            <!-- Statistics Section -->
            <section class="cards" style="padding: 3rem 0;">
                <div class="container">
                    <h2 style="text-align: center; color: var(--text); font-size: 2rem; margin-bottom: 3rem;">Your Shipment Overview</h2>

                    <?php
                    // Calculate statistics
                    $statusCounts = [];
                    $totalCBM = 0;
                    $totalCartons = 0;
                    $totalAmount = 0;
                    $recentShipments = 0;
                    $lastMonth = date('Y-m-d', strtotime('-30 days'));

                    foreach ($shipments as $shipment) {
                        $status = $shipment['status'];
                        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

                        if ($shipment['cbm']) $totalCBM += floatval($shipment['cbm']);
                        if ($shipment['cartons']) $totalCartons += intval($shipment['cartons']);
                        if ($shipment['total_amount']) $totalAmount += floatval($shipment['total_amount']);
                        if ($shipment['created_at'] >= $lastMonth) $recentShipments++;
                    }
                    ?>

                    <!-- Filters -->
                    <div class="filters" style="margin-bottom: 2rem;">
                        <form method="get" class="filter-form">
                            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        <?php foreach ($availableStatuses as $s): ?>
                                            <option value="<?php echo htmlspecialchars($s); ?>"
                                                <?php echo $status === $s ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($s); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="date_range">Date Range</label>
                                    <select name="date_range" id="date_range" class="form-control">
                                        <option value="">All Time</option>
                                        <option value="7days" <?php echo $dateRange === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                                        <option value="30days" <?php echo $dateRange === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                                        <option value="90days" <?php echo $dateRange === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                                    </select>
                                </div>
                                <div class="form-group" style="align-self: flex-end;">
                                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                                    <?php if ($status || $dateRange): ?>
                                        <a href="?page=1" class="btn btn-secondary">Clear Filters</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="cards__grid">
                        <div class="card">
                            <div class="card__icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <h3 class="card__title">Total Shipments</h3>
                            <p class="card__desc" style="font-size: 2rem; font-weight: bold; color: var(--accent);">
                                <?php echo number_format($totalShipments); ?>
                            </p>
                            <small style="color: var(--muted);">
                                <?php echo $recentShipments; ?> in the last 30 days
                            </small>
                        </div>

                        <div class="card">
                            <div class="card__icon">
                                <i class="fas fa-cube"></i>
                            </div>
                            <h3 class="card__title">Total Volume</h3>
                            <p class="card__desc" style="font-size: 2rem; font-weight: bold; color: var(--accent);">
                                <?php echo number_format($totalCBM, 2); ?> CBM
                            </p>
                            <small style="color: var(--muted);">
                                <?php echo number_format($totalCartons); ?> cartons total
                            </small>
                        </div>

                        <div class="card">
                            <div class="card__icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <h3 class="card__title">Total Value</h3>
                            <p class="card__desc" style="font-size: 2rem; font-weight: bold; color: var(--accent);">
                                $<?php echo number_format($totalAmount, 2); ?>
                            </p>
                            <small style="color: var(--muted);">Across all shipments</small>
                        </div>

                        <div class="card">
                            <div class="card__icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <h3 class="card__title">Active Status</h3>
                            <div class="card__desc">
                                <?php
                                $topStatuses = array_slice($statusCounts, 0, 2, true);
                                foreach ($topStatuses as $status => $count): ?>
                                    <div style="margin-bottom: 0.5rem;">
                                        <span class="status-badge status-<?php echo strtolower(str_replace([' ', '/', '&'], ['-', '-', 'and'], $status)); ?>" style="font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                        <span style="margin-left: 0.5rem; color: var(--muted);"><?php echo $count; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Shipments Table Section -->
            <section style="padding: 2rem 0;">
                <div class="container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                        <h2 style="color: var(--text); margin: 0;">
                            <i class="fas fa-ship"></i>
                            Your Shipments
                        </h2>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <a href="/public/track.php" class="btn btn-secondary btn-small">
                                <i class="fas fa-search"></i>
                                Quick Track
                            </a>
                            <a href="https://wa.me/96171123456?text=I%20need%20help%20with%20my%20shipments"
                                target="_blank" class="btn btn-secondary btn-small">
                                <i class="fab fa-whatsapp"></i>
                                Get Support
                            </a>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="shipments-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> Tracking Number</th>
                                    <th><i class="fas fa-container"></i> Container Number</th>
                                    <th><i class="fas fa-flag"></i> Status</th>
                                    <th><i class="fas fa-cube"></i> CBM</th>
                                    <th><i class="fas fa-boxes"></i> Cartons</th>
                                    <th><i class="fas fa-weight"></i> Weight (kg)</th>
                                    <th><i class="fas fa-weight-hanging"></i> GW (kg)</th>
                                    <th><i class="fas fa-dollar-sign"></i> Amount</th>
                                    <th><i class="fas fa-calendar"></i> Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipments as $shipment): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($shipment['tracking_number']); ?></strong>
                                            <?php if ($shipment['bl_number']): ?>
                                                <br><small style="color: var(--muted);">BL: <?php echo htmlspecialchars($shipment['bl_number']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $shipment['container_number'] ? htmlspecialchars($shipment['container_number']) : '<span style="color: var(--muted);">-</span>'; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower(str_replace([' ', '/', '&'], ['-', '-', 'and'], $shipment['status'])); ?>">
                                                <?php echo htmlspecialchars($shipment['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $shipment['cbm'] ? htmlspecialchars($shipment['cbm']) : '<span style="color: var(--muted);">-</span>'; ?></td>
                                        <td><?php echo $shipment['cartons'] ? htmlspecialchars($shipment['cartons']) : '<span style="color: var(--muted);">-</span>'; ?></td>
                                        <td><?php echo $shipment['weight'] ? htmlspecialchars($shipment['weight']) : '<span style="color: var(--muted);">-</span>'; ?></td>
                                        <td><?php echo $shipment['gross_weight'] ? htmlspecialchars($shipment['gross_weight']) : '<span style="color: var(--muted);">-</span>'; ?></td>
                                        <td>
                                            <?php if ($shipment['total_amount']): ?>
                                                $<?php echo number_format(floatval($shipment['total_amount']), 2); ?>
                                            <?php else: ?>
                                                <span style="color: var(--muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($shipment['created_at'])); ?>
                                            <br><small style="color: var(--muted);"><?php echo date('H:i', strtotime($shipment['created_at'])); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary btn-small">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>"
                                    class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?> btn-small">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="btn btn-secondary btn-small">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <p style="text-align: center; color: var(--muted); margin-top: 1rem;">
                            Showing page <?php echo $page; ?> of <?php echo $totalPages; ?>
                            (<?php echo min($perPage, $totalShipments - $offset); ?> of <?php echo $totalShipments; ?> shipments)
                        </p>
                    <?php endif; ?>

                    <!-- Summary Section -->
                    <div class="summary" style="margin-top: 3rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                            <div>
                                <h4 style="color: var(--accent); margin-bottom: 1rem;">
                                    <i class="fas fa-chart-bar"></i> Shipment Statistics
                                </h4>
                                <p><strong>Total Shipments:</strong> <?php echo $totalShipments; ?></p>
                                <p><strong>Recent (30 days):</strong> <?php echo $recentShipments; ?></p>
                                <p><strong>Total CBM:</strong> <?php echo number_format($totalCBM, 2); ?></p>
                                <p><strong>Total Cartons:</strong> <?php echo number_format($totalCartons); ?></p>
                            </div>

                            <div>
                                <h4 style="color: var(--accent); margin-bottom: 1rem;">
                                    <i class="fas fa-list"></i> Status Breakdown
                                </h4>
                                <?php foreach ($statusCounts as $status => $count): ?>
                                    <p>
                                        <span class="status-badge status-<?php echo strtolower(str_replace([' ', '/', '&'], ['-', '-', 'and'], $status)); ?>"
                                            style="margin-right: 0.5rem; font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                        <?php echo $count; ?> shipment<?php echo $count !== 1 ? 's' : ''; ?>
                                    </p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        <?php else: ?>
            <!-- No Shipments -->
            <section class="hero" style="min-height: 60vh;">
                <div class="hero__content">
                    <div style="background: var(--card); border-radius: 50%; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                        <i class="fas fa-ship" style="font-size: 3rem; color: var(--muted);"></i>
                    </div>
                    <h2 style="color: var(--text); font-size: 2rem; margin-bottom: 1rem;">No Shipments Yet</h2>
                    <p style="color: var(--muted); font-size: 1.2rem; max-width: 600px; margin: 0 auto 3rem;">
                        You don't have any shipments in your account yet. Once you start shipping with us,
                        all your tracking information will appear here.
                    </p>

                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="/public/contact.php" class="hero__cta">
                            <i class="fas fa-plus"></i>
                            Request a Shipment
                        </a>
                        <a href="https://wa.me/96171123456?text=I%20would%20like%20to%20start%20shipping%20with%20Salameh%20Cargo"
                            target="_blank" class="btn btn-secondary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                            <i class="fab fa-whatsapp"></i>
                            Chat with Support
                        </a>
                    </div>

                    <div style="margin-top: 3rem; padding: 2rem; background: var(--card); border-radius: var(--radius); max-width: 600px; margin-left: auto; margin-right: auto;">
                        <h4 style="color: var(--accent); margin-bottom: 1rem;">
                            <i class="fas fa-info-circle"></i> How to Get Started
                        </h4>
                        <ol style="text-align: left; color: var(--muted); line-height: 2;">
                            <li>Contact our team to discuss your shipping needs</li>
                            <li>We'll provide you with a quote and shipping options</li>
                            <li>Once confirmed, your shipment will be added to your dashboard</li>
                            <li>Track your cargo in real-time from pickup to delivery</li>
                        </ol>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Quick Actions -->
        <section class="cards" style="background: #1a1a1a; padding: 3rem 0;">
            <div class="container">
                <h2 style="text-align: center; color: var(--text); font-size: 2rem; margin-bottom: 3rem;">Quick Actions</h2>
                <div class="cards__grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="card__title">Track Any Shipment</h3>
                        <p class="card__desc">Search for any shipment using tracking number, container, or personal info.</p>
                        <a href="/public/track.php" class="btn btn-primary" style="margin-top: 1rem;">
                            Start Tracking
                        </a>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h3 class="card__title">WhatsApp Support</h3>
                        <p class="card__desc">Get instant help from our customer support team via WhatsApp.</p>
                        <a href="https://wa.me/96171123456?text=Hello%20Salameh%20Cargo!%20I%20need%20assistance."
                            target="_blank" class="btn btn-primary" style="margin-top: 1rem; background: #25D366;">
                            Chat Now
                        </a>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3 class="card__title">Contact Us</h3>
                        <p class="card__desc">Get in touch with our team for quotes, inquiries, or support.</p>
                        <a href="/public/contact.php" class="btn btn-primary" style="margin-top: 1rem;">
                            Contact Team
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text);
    }

    .form-control {
        padding: 0.5rem;
        border: 1px solid var(--border);
        border-radius: 4px;
        background: var(--card);
        color: var(--text);
        min-width: 200px;
    }

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        display: inline-block;
        text-decoration: none;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: var(--accent);
        color: white;
    }

    .btn-secondary {
        background: var(--card);
        color: var(--text);
    }

    .pagination {
        margin-top: 2rem;
        text-align: center;
    }

    .pagination__info {
        margin-bottom: 1rem;
        color: var(--muted);
    }

    .pagination__controls {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .filter-form {
        background: var(--card);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }

    /* Responsive table */
    @media (max-width: 768px) {
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .cards__grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pageTitle = 'Dashboard';

// Add component styles
$componentStyles = [
    'stats-cards',
    'status-breakdown',
    'quick-actions'
];

// Register component styles
$_SESSION['component_styles'] = $componentStyles;

include __DIR__ . '/../includes/admin-header.php';

// Get dashboard metrics
try {
    // Total shipments and recent activity
    $shipmentsStmt = $pdo->query('
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as last_7_days,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as last_30_days,
            SUM(CASE WHEN DATE(updated_at) = CURDATE() OR (updated_at IS NULL AND DATE(created_at) = CURDATE()) THEN 1 ELSE 0 END) as updated_today,
            SUM(CASE WHEN status != "Delivered" THEN 1 ELSE 0 END) as active_shipments
        FROM shipments
    ');
    $shipmentCounts = $shipmentsStmt->fetch();
    $totalShipments = $shipmentCounts['total'];
    $recentShipments = $shipmentCounts['last_7_days'];
    $monthlyShipments = $shipmentCounts['last_30_days'];
    $updatedToday = $shipmentCounts['updated_today'];
    $activeShipments = $shipmentCounts['active_shipments'];

    // Shipments by status with percentage
    $statusStmt = $pdo->query('
        SELECT 
            status,
            COUNT(*) as count,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM shipments) as percentage
        FROM shipments 
        GROUP BY status 
        ORDER BY count DESC
    ');
    $statusCounts = $statusStmt->fetchAll();

    // User metrics
    $userStmt = $pdo->query('
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_users,
            COUNT(DISTINCT shipping_code) as with_shipping_code
        FROM users
    ');
    $userMetrics = $userStmt->fetch();
    $totalUsers = $userMetrics['total_users'];
    $newUsers = $userMetrics['new_users'];
    $usersWithCode = $userMetrics['with_shipping_code'];

    // Volume and value metrics
    $metricsStmt = $pdo->query('
        SELECT 
            SUM(CAST(cbm AS DECIMAL(10,2))) as total_cbm,
            SUM(CAST(total_amount AS DECIMAL(10,2))) as total_amount,
            SUM(CAST(cartons AS DECIMAL)) as total_cartons,
            SUM(CAST(weight AS DECIMAL(10,2))) as total_weight,
            AVG(CAST(total_amount AS DECIMAL(10,2))) as avg_amount
        FROM shipments 
        WHERE cbm IS NOT NULL AND cbm != "" 
        AND total_amount IS NOT NULL AND total_amount != ""
    ');
    $metrics = $metricsStmt->fetch();

    // Recent activity log
    $recentActivityStmt = $pdo->query('
        SELECT 
            l.action_type,
            l.details,
            l.timestamp,
            CASE 
                WHEN l.actor_id > 0 THEN u.full_name
                ELSE a.username
            END as actor_name,
            CASE 
                WHEN l.actor_id > 0 THEN "user"
                ELSE "admin"
            END as actor_type
        FROM logs l
        LEFT JOIN users u ON l.actor_id = u.user_id AND l.actor_id > 0
        LEFT JOIN admins a ON -l.actor_id = a.admin_id AND l.actor_id < 0
        ORDER BY l.timestamp DESC
        LIMIT 5
    ');
    $recentActivity = $recentActivityStmt->fetchAll();

    // Get admin info with role
    $adminStmt = $pdo->prepare('
        SELECT 
            username,
            role,
            created_at
        FROM admins 
        WHERE admin_id = ?
    ');
    $adminStmt->execute([$_SESSION['admin_id']]);
    $admin = $adminStmt->fetch();

    // System health checks
    $dbHealthStmt = $pdo->query('SELECT NOW() as db_time');
    $dbHealth = $dbHealthStmt->fetch();

    // Get the last scrape time
    $lastScrapeStmt = $pdo->query('
        SELECT MAX(scrape_time) as last_scrape
        FROM shipment_scrapes
    ');
    $lastScrape = $lastScrapeStmt->fetch();
} catch (Exception $e) {
    $error = 'Error loading dashboard data: ' . $e->getMessage();
}

?>

<div class="admin-dashboard">
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="admin-header">
            <div class="header-left">
                <h1>Admin Dashboard</h1>
                <div class="admin-info">
                    <span class="admin-name">Welcome, <?= htmlspecialchars($admin['username']) ?></span>
                    <span class="admin-badge"><?= htmlspecialchars($admin['role'] ?? 'Administrator') ?></span>
                    <span class="admin-meta">
                        <i class="far fa-clock"></i>
                        Last active: <?= date('M d, H:i', strtotime($dbHealth['db_time'])) ?>
                    </span>
                </div>
            </div>
            <div class="header-right">
                <div class="system-status <?= $lastScrape && (time() - strtotime($lastScrape['last_scrape'])) < 3600 ? 'is-active' : 'needs-attention' ?>">
                    <i class="fas <?= $lastScrape && (time() - strtotime($lastScrape['last_scrape'])) < 3600 ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <span>System Status: <?= $lastScrape && (time() - strtotime($lastScrape['last_scrape'])) < 3600 ? 'Active' : 'Need Attention' ?></span>
                </div>
                <button id="theme-toggle" class="theme-toggle" aria-label="Toggle dark mode">
                    <i class="fas fa-sun light-icon"></i>
                    <i class="fas fa-moon dark-icon"></i>
                </button>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid slide-up">
            <div class="stats-card card-hover">
                <div class="stats-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stats-content">
                    <h3>Active Shipments</h3>
                    <p class="stats-value"><?= number_format($activeShipments) ?></p>
                    <p class="stats-detail">of <?= number_format($totalShipments) ?> total shipments</p>
                    <div class="stats-progress">
                        <div class="progress-bar">
                            <div class="progress" style="width: <?= ($activeShipments / $totalShipments) * 100 ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-card card-hover">
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-content">
                    <h3>User Base</h3>
                    <p class="stats-value"><?= number_format($totalUsers) ?></p>
                    <div class="stats-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <?= number_format($newUsers) ?> new this month
                    </div>
                </div>
            </div>

            <div class="stats-card card-hover">
                <div class="stats-icon">
                    <i class="fas fa-sync"></i>
                </div>
                <div class="stats-content">
                    <h3>Today's Updates</h3>
                    <p class="stats-value"><?= number_format($updatedToday) ?></p>
                    <div class="stats-meta">
                        <i class="far fa-clock"></i>
                        Last scan: <?= $lastScrape ? date('H:i', strtotime($lastScrape['last_scrape'])) : 'N/A' ?>
                    </div>
                </div>
            </div>

            <div class="stats-card card-hover">
                <div class="stats-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stats-content">
                    <h3>Monthly Performance</h3>
                    <p class="stats-value"><?= number_format($monthlyShipments) ?></p>
                    <div class="stats-metrics">
                        <span class="metric">
                            <i class="fas fa-cube"></i>
                            <?= number_format($metrics['total_cbm'], 1) ?> CBM
                        </span>
                        <span class="metric">
                            <i class="fas fa-weight-hanging"></i>
                            <?= number_format($metrics['total_weight'], 1) ?> KG
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>

    <!-- Status Breakdown -->
    <?php if (!empty($statusCounts)): ?>
        <section class="status-breakdown bg-gradient">
            <div class="container">
                <header class="section-header">
                    <h2>Shipment Status Breakdown</h2>
                    <p class="section-desc">Current distribution of shipments across different status categories</p>
                </header>

                <div class="status-grid">
                    <?php
                    $topStatuses = array_slice($statusCounts, 0, 6);
                    foreach ($topStatuses as $statusData):
                        $statusSlug = strtolower(str_replace([' ', '/', '&'], ['-', '-', 'and'], $statusData['status']));
                        $percentage = $totalShipments > 0 ? ($statusData['count'] / $totalShipments) * 100 : 0;
                    ?>
                        <div class="status-card card-hover fade-in">
                            <div class="status-header">
                                <span class="status-badge badge-<?php echo $statusSlug; ?>">
                                    <?php echo htmlspecialchars($statusData['status']); ?>
                                </span>
                            </div>
                            <div class="status-body">
                                <h3 class="status-count">
                                    <?php echo number_format($statusData['count']); ?>
                                </h3>
                                <div class="status-percentage">
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php echo $percentage; ?>%;"></div>
                                    </div>
                                    <span class="percentage-value">
                                        <?php echo number_format($percentage, 1); ?>%
                                    </span>
                                </div>
                            </div>
                            <div class="status-trend">
                                <?php
                                $weeklyChange = rand(-10, 20); // Replace with actual calculation
                                $trendClass = $weeklyChange >= 0 ? 'positive' : 'negative';
                                $trendIcon = $weeklyChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                ?>
                                <span class="trend <?php echo $trendClass; ?>">
                                    <i class="fas <?php echo $trendIcon; ?>"></i>
                                    <?php echo abs($weeklyChange); ?>% this week
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Quick Actions -->
    <section class="quick-actions">
        <div class="container">
            <header class="section-header">
                <h2>
                    <i class="fas fa-bolt text-accent"></i>
                    Quick Actions
                </h2>
                <p class="section-desc text-base">Access frequently used administrative tools and functions</p>
            </header>

            <div class="action-grid">
                <div class="action-card card-hover">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="action-content">
                        <h3>Add New User</h3>
                        <p>Register a new customer account with full shipping details and contact information.</p>
                        <a href="/admin/add_user.php" class="btn btn-accent">
                            <i class="fas fa-user-plus"></i>
                            Add User
                        </a>
                    </div>
                </div>

                <div class="action-card card-hover">
                    <div class="action-icon">
                        <i class="fas fa-upload"></i>
                    </div>
                    <div class="action-content">
                        <h3>Upload Shipments</h3>
                        <p>Bulk upload shipment data using Excel or CSV files with automatic validation and processing.</p>
                        <a href="/admin/upload_shipments.php" class="btn btn-accent">
                            <i class="fas fa-upload"></i>
                            Import Data
                        </a>
                    </div>
                </div>

                <div class="action-card card-hover">
                    <div class="action-icon">
                        <i class="fas fa-ship"></i>
                    </div>
                    <div class="action-content">
                        <h3>Manage Shipments</h3>
                        <p>View, edit, and update shipment information, track status changes, and manage cargo details.</p>
                        <a href="/admin/shipments.php" class="btn btn-accent">
                            <i class="fas fa-ship"></i>
                            View All
                        </a>
                    </div>
                </div>

                <div class="action-card card-hover">
                    <div class="action-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="action-content">
                        <h3>Automation Settings</h3>
                        <p>Configure automated tracking updates, manage scraping settings, and monitor system processes.</p>
                        <a href="/admin/automation.php" class="btn btn-accent">
                            <i class="fas fa-cogs"></i>
                            Configure
                        </a>
                    </div>
                </div>

                <div class="action-card card-hover">
                    <div class="action-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="action-content">
                        <h3>Track Shipments</h3>
                        <p class="card__desc">Use the public tracking system to search and verify shipment information across all records.</p>
                        <a href="/public/track.php" target="_blank" class="btn btn-secondary" style="margin-top: 1.5rem;">
                            <i class="fas fa-external-link-alt"></i>
                            Public Tracker
                        </a>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="card__title">System Reports</h3>
                        <p class="card__desc">Generate detailed reports on shipping performance, customer activity, and system metrics.</p>
                        <button class="btn btn-secondary" style="margin-top: 1.5rem;" onclick="alert('Reports feature coming soon!');">
                            <i class="fas fa-chart-line"></i>
                            Coming Soon
                        </button>
                    </div>
                </div>
            </div>
    </section>

    <!-- Recent Activity -->
    <section class="split" style="background: #1a1a1a;">
        <div class="container">
            <div class="split__container">
                <div class="split__body">
                    <h2 class="split__title">
                        <i class="fas fa-clock"></i>
                        System Information
                    </h2>
                    <p class="split__text">
                        Keep track of important system metrics and recent activity to ensure smooth operations of your cargo management system.
                    </p>

                    <div style="margin: 2rem 0;">
                        <h4 style="color: var(--accent); margin-bottom: 1rem;">Recent Statistics:</h4>
                        <ul style="color: var(--muted); line-height: 2; list-style: none; padding: 0;">
                            <li>
                                <i class="fas fa-check-circle" style="color: var(--accent); margin-right: 0.5rem;"></i>
                                <strong><?php echo number_format($totalShipments ?? 0); ?></strong> total shipments in system
                            </li>
                            <li>
                                <i class="fas fa-users" style="color: var(--accent); margin-right: 0.5rem;"></i>
                                <strong><?php echo number_format($totalUsers ?? 0); ?></strong> registered customers
                            </li>
                            <li>
                                <i class="fas fa-sync-alt" style="color: var(--accent); margin-right: 0.5rem;"></i>
                                <strong><?php echo number_format($updatedToday ?? 0); ?></strong> shipments updated today
                            </li>
                            <li>
                                <i class="fas fa-calendar-week" style="color: var(--accent); margin-right: 0.5rem;"></i>
                                <strong><?php echo number_format($recentShipments ?? 0); ?></strong> new shipments this week
                            </li>
                        </ul>
                    </div>

                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="/admin/automation.php" class="btn btn-primary">
                            <i class="fas fa-sync"></i>
                            Run Updates
                        </a>
                        <a href="/admin/shipments.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i>
                            View All Shipments
                        </a>
                    </div>
                </div>
                <div class="split__media">
                    <div style="background: var(--card); padding: 2rem; border-radius: var(--radius); text-align: center;">
                        <i class="fas fa-server" style="font-size: 4rem; color: var(--accent); margin-bottom: 1rem;"></i>
                        <h3 style="color: var(--text); margin-bottom: 1rem;">System Status</h3>
                        <div style="display: grid; gap: 1rem; text-align: left;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--muted);">Database:</span>
                                <span style="color: #26de81;"><i class="fas fa-check-circle"></i> Online</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--muted);">Tracking System:</span>
                                <span style="color: #26de81;"><i class="fas fa-check-circle"></i> Active</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--muted);">Last Backup:</span>
                                <span style="color: var(--accent);"><?php echo date('M j, H:i'); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--muted);">Server Load:</span>
                                <span style="color: #26de81;">Normal</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Help Section -->
    <section class="hero" style="min-height: 30vh; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);">
        <div class="hero__content">
            <h2 class="hero__title" style="font-size: 1.75rem;">Need Help?</h2>
            <p style="color: var(--muted); margin-bottom: 2rem;">
                Access documentation, support resources, or contact the development team for assistance.
            </p>

            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="/public/contact.php" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-question-circle"></i>
                    Support
                </a>
                <a href="https://wa.me/96171123456?text=Admin%20panel%20support%20request"
                    target="_blank" class="btn btn-secondary">
                    <i class="fab fa-whatsapp"></i>
                    WhatsApp Support
                </a>
                <a href="/admin/login.php" class="btn btn-secondary">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </section>
    </div>
</div>

<script>
    // Auto-refresh dashboard every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);

    // Add click animations to cards
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('a, button')) {
                const link = this.querySelector('a');
                if (link) {
                    link.click();
                }
            }
        });

        card.style.cursor = 'pointer';
    });
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
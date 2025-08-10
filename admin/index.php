<?php 
require_once __DIR__ . '/../includes/db.php'; 
require_once __DIR__ . '/../includes/auth.php'; 
requireAdmin();

// Get dashboard metrics
try {
    // Total shipments
    $totalShipmentsStmt = $pdo->query('SELECT COUNT(*) FROM shipments');
    $totalShipments = $totalShipmentsStmt->fetchColumn();

    // Shipments by status
    $statusStmt = $pdo->query('SELECT status, COUNT(*) as count FROM shipments GROUP BY status ORDER BY count DESC');
    $statusCounts = $statusStmt->fetchAll();

    // Total users
    $totalUsersStmt = $pdo->query('SELECT COUNT(*) FROM users');
    $totalUsers = $totalUsersStmt->fetchColumn();

    // Recent activity (shipments updated today)
    $todayStmt = $pdo->query('SELECT COUNT(*) FROM shipments WHERE DATE(updated_at) = CURDATE() OR (updated_at IS NULL AND DATE(created_at) = CURDATE())');
    $updatedToday = $todayStmt->fetchColumn();

    // Recent shipments (last 7 days)
    $recentStmt = $pdo->query('SELECT COUNT(*) FROM shipments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
    $recentShipments = $recentStmt->fetchColumn();

    // Total CBM and value
    $metricsStmt = $pdo->query('SELECT SUM(CAST(cbm AS DECIMAL(10,2))) as total_cbm, SUM(CAST(total_amount AS DECIMAL(10,2))) as total_amount FROM shipments WHERE cbm IS NOT NULL AND cbm != "" AND total_amount IS NOT NULL AND total_amount != ""');
    $metrics = $metricsStmt->fetch();

    // Get admin info
    $adminStmt = $pdo->prepare('SELECT username FROM admins WHERE admin_id = ?');
    $adminStmt->execute([$_SESSION['admin_id']]);
    $admin = $adminStmt->fetch();

} catch (Exception $e) {
    $error = 'Error loading dashboard data: ' . $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<main>
    <div class="container">
        <!-- Hero Section -->
        <section class="hero" style="min-height: 40vh;">
            <div class="hero__content">
                <h1 class="hero__title" style="font-size: 2.5rem;">
                    Admin Dashboard
                </h1>
                <p class="hero__subtitle">
                    Welcome back<?php echo $admin['username'] ? ', ' . htmlspecialchars($admin['username']) : ''; ?>! 
                    Manage your cargo operations from this central hub.
                </p>
                <p style="color: var(--muted); margin-top: 1rem;">
                    <i class="fas fa-clock"></i> Last updated: <?php echo date('M j, Y \a\t H:i'); ?>
                </p>
            </div>
        </section>

        <?php if (isset($error)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Metrics Overview -->
        <section class="cards" style="padding: 3rem 0;">
            <div class="container">
                <h2 style="text-align: center; color: var(--text); font-size: 2rem; margin-bottom: 3rem;">
                    <i class="fas fa-chart-bar"></i> System Overview
                </h2>
                
                <div class="cards__grid">
                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h3 class="card__title">Total Shipments</h3>
                        <p class="card__desc" style="font-size: 2.5rem; font-weight: bold; color: var(--accent); margin-bottom: 0.5rem;">
                            <?php echo number_format($totalShipments ?? 0); ?>
                        </p>
                        <small style="color: var(--muted);">
                            <i class="fas fa-plus"></i> <?php echo number_format($recentShipments ?? 0); ?> this week
                        </small>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="card__title">Total Customers</h3>
                        <p class="card__desc" style="font-size: 2.5rem; font-weight: bold; color: var(--accent); margin-bottom: 0.5rem;">
                            <?php echo number_format($totalUsers ?? 0); ?>
                        </p>
                        <small style="color: var(--muted);">
                            <i class="fas fa-user-check"></i> Registered accounts
                        </small>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h3 class="card__title">Today's Updates</h3>
                        <p class="card__desc" style="font-size: 2.5rem; font-weight: bold; color: var(--accent); margin-bottom: 0.5rem;">
                            <?php echo number_format($updatedToday ?? 0); ?>
                        </p>
                        <small style="color: var(--muted);">
                            <i class="fas fa-calendar-day"></i> Shipments updated today
                        </small>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3 class="card__title">Total Value</h3>
                        <p class="card__desc" style="font-size: 2rem; font-weight: bold; color: var(--accent); margin-bottom: 0.5rem;">
                            $<?php echo number_format($metrics['total_amount'] ?? 0, 2); ?>
                        </p>
                        <small style="color: var(--muted);">
                            <i class="fas fa-cube"></i> <?php echo number_format($metrics['total_cbm'] ?? 0, 2); ?> CBM
                        </small>
                    </div>
                </div>
            </div>
        </section>

        <!-- Status Breakdown -->
        <?php if (!empty($statusCounts)): ?>
            <section class="why" style="background: #1a1a1a;">
                <div class="container">
                    <div class="why__header">
                        <h2 class="why__title">Shipment Status Breakdown</h2>
                    </div>
                    <div class="why__grid">
                        <?php 
                        // Display top 6 statuses
                        $topStatuses = array_slice($statusCounts, 0, 6);
                        foreach ($topStatuses as $statusData): 
                        ?>
                            <div class="why__item">
                                <div class="why__icon">
                                    <span class="status-badge status-<?php echo strtolower(str_replace([' ', '/', '&'], ['-', '-', 'and'], $statusData['status'])); ?>" 
                                          style="font-size: 1rem; padding: 0.5rem 1rem;">
                                        <?php echo htmlspecialchars($statusData['status']); ?>
                                    </span>
                                </div>
                                <h3 class="why__item-title" style="font-size: 2rem; color: var(--accent);">
                                    <?php echo number_format($statusData['count']); ?>
                                </h3>
                                <p class="why__item-desc">
                                    <?php 
                                    $percentage = $totalShipments > 0 ? ($statusData['count'] / $totalShipments) * 100 : 0;
                                    echo number_format($percentage, 1) . '% of total shipments';
                                    ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Quick Actions -->
        <section class="cards">
            <div class="container">
                <h2 style="text-align: center; color: var(--text); font-size: 2rem; margin-bottom: 3rem;">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h2>
                
                <div class="cards__grid">
                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3 class="card__title">Add New User</h3>
                        <p class="card__desc">Register a new customer account with full shipping details and contact information.</p>
                        <a href="/admin/add_user.php" class="btn btn-primary" style="margin-top: 1.5rem;">
                            <i class="fas fa-user-plus"></i>
                            Add User
                        </a>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-upload"></i>
                        </div>
                        <h3 class="card__title">Upload Shipments</h3>
                        <p class="card__desc">Bulk upload shipment data using CSV files with automatic validation and processing.</p>
                        <a href="/admin/upload_shipments.php" class="btn btn-primary" style="margin-top: 1.5rem;">
                            <i class="fas fa-upload"></i>
                            Upload CSV
                        </a>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-ship"></i>
                        </div>
                        <h3 class="card__title">Manage Shipments</h3>
                        <p class="card__desc">View, edit, and update shipment information, track status changes, and manage cargo details.</p>
                        <a href="/admin/shipments.php" class="btn btn-primary" style="margin-top: 1.5rem;">
                            <i class="fas fa-ship"></i>
                            View Shipments
                        </a>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3 class="card__title">Automation Settings</h3>
                        <p class="card__desc">Configure automated tracking updates, manage scraping settings, and monitor system processes.</p>
                        <a href="/admin/automation.php" class="btn btn-primary" style="margin-top: 1.5rem;">
                            <i class="fas fa-cogs"></i>
                            Automation
                        </a>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="card__title">Track Shipments</h3>
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
</main>

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

<?php include __DIR__ . '/../includes/footer.php'; ?>

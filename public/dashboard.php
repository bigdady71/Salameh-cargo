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
        <section class="welcome-section">
            <div class="welcome-content">
                <div class="welcome-header">
                    <div class="welcome-title">
                        <h1>
                            Welcome Back<?php echo $user['full_name'] ? ', ' . htmlspecialchars($user['full_name']) : ''; ?>!
                        </h1>
                        <p class="welcome-subtitle">Manage and track all your shipments from your personal dashboard.</p>
                    </div>
                    <div class="welcome-actions">
                        <a href="/public/track.php" class="btn btn-primary">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            Track Shipment
                        </a>
                        <a href="https://wa.me/96171123456" class="btn btn-whatsapp" target="_blank">
                            <i class="fab fa-whatsapp" aria-hidden="true"></i>
                            Support
                        </a>
                    </div>
                </div>

                <?php if ($user['phone'] || $user['email']): ?>
                    <div class="contact-info">
                        <?php if ($user['phone']): ?>
                            <div class="contact-item">
                                <i class="fas fa-phone" aria-hidden="true"></i>
                                <span><?php echo htmlspecialchars($user['phone']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($user['email']): ?>
                            <div class="contact-item">
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Dashboard Filters -->
            <div class="filter-section">
                <form method="get" class="filter-form" role="search">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-filter" aria-hidden="true"></i>
                                Filter by Status
                            </label>
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
                            <label for="date_range">
                                <i class="fas fa-calendar" aria-hidden="true"></i>
                                Time Period
                            </label>
                            <select name="date_range" id="date_range" class="form-control">
                                <option value="">All Time</option>
                                <option value="7days" <?php echo $dateRange === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                                <option value="30days" <?php echo $dateRange === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                                <option value="90days" <?php echo $dateRange === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search" aria-hidden="true"></i>
                                Apply Filters
                            </button>
                            <?php if ($status || $dateRange): ?>
                                <a href="?page=1" class="btn btn-secondary">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                    Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($status || $dateRange): ?>
                        <div class="active-filters">
                            <span class="filter-label">Active Filters:</span>
                            <?php if ($status): ?>
                                <span class="filter-tag">
                                    Status: <?php echo htmlspecialchars($status); ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['status' => ''])); ?>"
                                        class="remove-filter" aria-label="Remove status filter">×</a>
                                </span>
                            <?php endif; ?>
                            <?php if ($dateRange): ?>
                                <span class="filter-tag">
                                    <?php
                                    $rangeName = [
                                        '7days' => 'Last 7 Days',
                                        '30days' => 'Last 30 Days',
                                        '90days' => 'Last 90 Days'
                                    ][$dateRange] ?? $dateRange;
                                    echo htmlspecialchars($rangeName);
                                    ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['date_range' => ''])); ?>"
                                        class="remove-filter" aria-label="Remove date range filter">×</a>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </section>

        <style>
            .welcome-section {
                padding: 2rem 0;
                background: var(--card);
                border-radius: 12px;
                margin-bottom: 2rem;
            }

            .welcome-content {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 1rem;
            }

            .welcome-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 1rem;
                margin-bottom: 1.5rem;
            }

            .welcome-title h1 {
                font-size: 2.5rem;
                color: var(--text);
                margin: 0;
                line-height: 1.2;
            }

            .welcome-subtitle {
                color: var(--muted);
                font-size: 1.1rem;
                margin: 0.5rem 0 0;
            }

            .welcome-actions {
                display: flex;
                gap: 1rem;
            }

            .contact-info {
                display: flex;
                gap: 2rem;
                flex-wrap: wrap;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid var(--border);
            }

            .contact-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: var(--muted);
            }

            .btn-whatsapp {
                background: #25D366;
                color: white;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                border-radius: 6px;
                text-decoration: none;
                transition: all 0.2s ease;
            }

            .btn-whatsapp:hover {
                background: #128C7E;
            }

            .filter-section {
                background: var(--background);
                border-radius: 12px;
                margin: 0 1rem;
                margin-top: 2rem;
            }

            .filter-form {
                padding: 1.5rem;
            }

            .filter-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.5rem;
                align-items: end;
            }

            .form-control {
                width: 100%;
                padding: 0.75rem;
                border: 2px solid var(--border);
                border-radius: 6px;
                background: var(--card);
                color: var(--text);
                transition: all 0.2s ease;
            }

            .form-control:focus {
                border-color: var(--accent);
                box-shadow: 0 0 0 2px var(--accent-transparent);
                outline: none;
            }

            .filter-actions {
                display: flex;
                gap: 0.5rem;
            }

            .active-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid var(--border);
            }

            .filter-label {
                color: var(--muted);
                font-size: 0.9rem;
                padding: 0.25rem 0;
            }

            .filter-tag {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.25rem 0.75rem;
                background: var(--card);
                border-radius: 100px;
                font-size: 0.9rem;
                color: var(--text);
            }

            .remove-filter {
                color: var(--muted);
                text-decoration: none;
                font-weight: bold;
                font-size: 1.2rem;
                line-height: 1;
            }

            .remove-filter:hover {
                color: var(--accent);
            }

            @media (max-width: 768px) {
                .welcome-section {
                    border-radius: 0;
                }

                .welcome-header {
                    flex-direction: column;
                    text-align: center;
                }

                .welcome-title h1 {
                    font-size: 2rem;
                }

                .welcome-actions {
                    width: 100%;
                    justify-content: center;
                }

                .contact-info {
                    justify-content: center;
                }

                .filter-section {
                    margin: 1rem 0;
                    border-radius: 0;
                }

                .filter-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <?php if ($shipments): ?>
            <!-- Statistics Section -->
            <section class="stats-section">
                <?php
                // Calculate statistics
                $statusCounts = [];
                $totalCBM = 0;
                $totalCartons = 0;
                $totalAmount = 0;
                $totalWeight = 0;
                $recentShipments = 0;
                $lastMonth = date('Y-m-d', strtotime('-30 days'));
                $activeShipments = 0;

                foreach ($shipments as $shipment) {
                    $status = $shipment['status'];
                    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

                    if ($status !== 'Delivered') {
                        $activeShipments++;
                    }

                    if ($shipment['cbm']) $totalCBM += floatval($shipment['cbm']);
                    if ($shipment['cartons']) $totalCartons += intval($shipment['cartons']);
                    if ($shipment['weight']) $totalWeight += floatval($shipment['weight']);
                    if ($shipment['total_amount']) $totalAmount += floatval($shipment['total_amount']);
                    if ($shipment['created_at'] >= $lastMonth) $recentShipments++;
                }

                // Calculate status percentages
                arsort($statusCounts);
                $totalCount = array_sum($statusCounts);
                $statusPercentages = array_map(function ($count) use ($totalCount) {
                    return round(($count / $totalCount) * 100);
                }, $statusCounts);
                ?>

                <div class="stats-grid">
                    <!-- Primary Stats -->
                    <div class="stat-card primary">
                        <div class="stat-icon">
                            <i class="fas fa-boxes" aria-hidden="true"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-header">
                                <h3>Total Shipments</h3>
                                <span class="stat-change positive">
                                    <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                    <?php echo $recentShipments; ?> new in 30 days
                                </span>
                            </div>
                            <div class="stat-value"><?php echo number_format($totalShipments); ?></div>
                            <div class="stat-subtitle">
                                <?php echo $activeShipments; ?> active shipments
                            </div>
                        </div>
                    </div>

                    <!-- Volume Stats -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-cube" aria-hidden="true"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-header">
                                <h3>Total Volume</h3>
                            </div>
                            <div class="stat-value"><?php echo number_format($totalCBM, 2); ?> <span class="stat-unit">CBM</span></div>
                            <div class="stat-metrics">
                                <span class="metric">
                                    <i class="fas fa-box" aria-hidden="true"></i>
                                    <?php echo number_format($totalCartons); ?> cartons
                                </span>
                                <span class="metric">
                                    <i class="fas fa-weight-hanging" aria-hidden="true"></i>
                                    <?php echo number_format($totalWeight, 2); ?> kg
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Value Stats -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign" aria-hidden="true"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-header">
                                <h3>Total Value</h3>
                            </div>
                            <div class="stat-value">$<?php echo number_format($totalAmount, 2); ?></div>
                            <div class="stat-subtitle">
                                Avg: $<?php echo number_format($totalAmount / ($totalShipments ?: 1), 2); ?> per shipment
                            </div>
                        </div>
                    </div>

                    <!-- Status Distribution -->
                    <div class="stat-card wide">
                        <div class="stat-header">
                            <h3>Status Distribution</h3>
                        </div>
                        <div class="status-distribution">
                            <?php foreach ($statusCounts as $status => $count): ?>
                                <div class="status-bar">
                                    <div class="status-info">
                                        <span class="status-badge status-<?php echo strtolower(str_replace([' ', '/', '&'], ['-', '-', 'and'], $status)); ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                        <span class="status-count"><?php echo $count; ?></span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php echo $statusPercentages[$status]; ?>%;"></div>
                                    </div>
                                    <span class="status-percentage"><?php echo $statusPercentages[$status]; ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <style>
                    .stats-section {
                        padding: 0 1rem;
                        margin-bottom: 2rem;
                    }

                    .stats-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                        gap: 1.5rem;
                        margin-bottom: 2rem;
                    }

                    .stat-card {
                        background: var(--card);
                        border-radius: 12px;
                        padding: 1.5rem;
                        display: flex;
                        gap: 1rem;
                        transition: transform 0.2s ease;
                    }

                    .stat-card:hover {
                        transform: translateY(-2px);
                    }

                    .stat-card.primary {
                        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
                        color: white;
                    }

                    .stat-card.wide {
                        grid-column: 1 / -1;
                    }

                    .stat-icon {
                        width: 48px;
                        height: 48px;
                        background: rgba(255, 255, 255, 0.1);
                        border-radius: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 1.5rem;
                    }

                    .stat-content {
                        flex: 1;
                    }

                    .stat-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 0.5rem;
                    }

                    .stat-header h3 {
                        font-size: 1rem;
                        font-weight: 500;
                        margin: 0;
                        color: inherit;
                    }

                    .stat-value {
                        font-size: 2rem;
                        font-weight: 600;
                        line-height: 1.2;
                        margin: 0.5rem 0;
                    }

                    .stat-unit {
                        font-size: 1rem;
                        opacity: 0.7;
                    }

                    .stat-subtitle {
                        font-size: 0.9rem;
                        color: var(--muted);
                    }

                    .primary .stat-subtitle {
                        color: rgba(255, 255, 255, 0.7);
                    }

                    .stat-change {
                        font-size: 0.85rem;
                        display: flex;
                        align-items: center;
                        gap: 0.25rem;
                    }

                    .stat-change.positive {
                        color: #4CAF50;
                    }

                    .primary .stat-change.positive {
                        color: rgba(255, 255, 255, 0.9);
                    }

                    .stat-metrics {
                        display: flex;
                        gap: 1rem;
                        margin-top: 0.5rem;
                    }

                    .metric {
                        font-size: 0.9rem;
                        color: var(--muted);
                        display: flex;
                        align-items: center;
                        gap: 0.35rem;
                    }

                    .status-distribution {
                        display: flex;
                        flex-direction: column;
                        gap: 1rem;
                        margin-top: 1rem;
                    }

                    .status-bar {
                        display: grid;
                        grid-template-columns: 200px 1fr auto;
                        align-items: center;
                        gap: 1rem;
                    }

                    .status-info {
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    }

                    .status-count {
                        color: var(--muted);
                        font-size: 0.9rem;
                    }

                    .progress-bar {
                        height: 8px;
                        background: var(--border);
                        border-radius: 4px;
                        overflow: hidden;
                    }

                    .progress {
                        height: 100%;
                        background: var(--accent);
                        border-radius: 4px;
                        transition: width 0.3s ease;
                    }

                    .status-percentage {
                        font-size: 0.9rem;
                        color: var(--muted);
                        min-width: 40px;
                        text-align: right;
                    }

                    @media (max-width: 768px) {
                        .stats-section {
                            padding: 0;
                        }

                        .stat-card {
                            padding: 1rem;
                        }

                        .status-bar {
                            grid-template-columns: 1fr;
                            gap: 0.5rem;
                        }

                        .status-info {
                            justify-content: space-between;
                        }

                        .status-percentage {
                            text-align: left;
                        }
                    }
                </style>

                <!-- Shipments Table Section -->
                <section class="shipments-section">
                    <div class="section-header">
                        <div class="section-title">
                            <h2>
                                <i class="fas fa-ship" aria-hidden="true"></i>
                                Your Shipments
                            </h2>
                            <span class="section-subtitle">
                                Showing <?php echo min($perPage, $totalShipments - $offset); ?> of <?php echo $totalShipments; ?> shipments
                            </span>
                        </div>
                        <div class="section-actions">
                            <a href="/public/track.php" class="btn btn-secondary">
                                <i class="fas fa-search" aria-hidden="true"></i>
                                Quick Track
                            </a>
                            <a href="https://wa.me/96171123456?text=I%20need%20help%20with%20my%20shipments"
                                target="_blank" class="btn btn-whatsapp">
                                <i class="fab fa-whatsapp" aria-hidden="true"></i>
                                Get Support
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="shipments-table">
                            <thead>
                                <tr>
                                    <th data-label="Tracking">
                                        <i class="fas fa-hashtag" aria-hidden="true"></i>
                                        <span>Tracking & BL</span>
                                    </th>
                                    <th data-label="Container">
                                        <i class="fas fa-container" aria-hidden="true"></i>
                                        <span>Container</span>
                                    </th>
                                    <th data-label="Status">
                                        <i class="fas fa-flag" aria-hidden="true"></i>
                                        <span>Status</span>
                                    </th>
                                    <th data-label="Metrics">
                                        <i class="fas fa-cube" aria-hidden="true"></i>
                                        <span>Metrics</span>
                                    </th>
                                    <th data-label="Weight">
                                        <i class="fas fa-weight-hanging" aria-hidden="true"></i>
                                        <span>Weight</span>
                                    </th>
                                    <th data-label="Amount">
                                        <i class="fas fa-dollar-sign" aria-hidden="true"></i>
                                        <span>Amount</span>
                                    </th>
                                    <th data-label="Updates">
                                        <i class="fas fa-clock" aria-hidden="true"></i>
                                        <span>Updates</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipments as $shipment): ?>
                                    <tr>
                                        <td data-label="Tracking">
                                            <div class="identifier-group">
                                                <div class="primary">
                                                    <?php echo htmlspecialchars($shipment['tracking_number']); ?>
                                                </div>
                                                <?php if ($shipment['bl_number']): ?>
                                                    <div class="secondary">
                                                        BL: <?php echo htmlspecialchars($shipment['bl_number']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td data-label="Container">
                                            <div class="identifier-group">
                                                <?php if ($shipment['container_number']): ?>
                                                    <div class="primary">
                                                        <?php echo htmlspecialchars($shipment['container_number']); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="empty">Not Available</div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td data-label="Status">
                                            <span class="status-badge status-<?php echo strtolower(str_replace([' ', '/', '&'], ['-', '-', 'and'], $shipment['status'])); ?>">
                                                <?php echo htmlspecialchars($shipment['status']); ?>
                                            </span>
                                        </td>
                                        <td data-label="Metrics">
                                            <div class="metrics-grid">
                                                <?php if ($shipment['cbm']): ?>
                                                    <div class="metrics-item">
                                                        <span class="metrics-label">CBM:</span>
                                                        <span class="metrics-value"><?php echo number_format($shipment['cbm'], 2); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($shipment['cartons']): ?>
                                                    <div class="metrics-item">
                                                        <span class="metrics-label">Cartons:</span>
                                                        <span class="metrics-value"><?php echo number_format($shipment['cartons']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td data-label="Weight">
                                            <?php if ($shipment['weight'] || $shipment['gross_weight']): ?>
                                                <div class="metrics-grid">
                                                    <?php if ($shipment['weight']): ?>
                                                        <div class="metrics-item">
                                                            <span class="metrics-label">NW:</span>
                                                            <span class="metrics-value"><?php echo number_format($shipment['weight'], 2); ?> kg</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($shipment['gross_weight']): ?>
                                                        <div class="metrics-item">
                                                            <span class="metrics-label">GW:</span>
                                                            <span class="metrics-value"><?php echo number_format($shipment['gross_weight'], 2); ?> kg</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="empty">No weight data</div>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Amount">
                                            <?php if ($shipment['total_amount']): ?>
                                                <div class="amount">
                                                    <span class="currency">$</span>
                                                    <span class="value"><?php echo number_format($shipment['total_amount'], 2); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <div class="empty">No amount</div>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Updates">
                                            <?php
                                            $lastUpdate = $shipment['last_update'];
                                            $timestamp = strtotime($lastUpdate);
                                            $now = time();
                                            $diff = $now - $timestamp;

                                            if ($diff < 3600) {
                                                $timeAgo = floor($diff / 60) . ' minutes ago';
                                            } elseif ($diff < 86400) {
                                                $timeAgo = floor($diff / 3600) . ' hours ago';
                                            } elseif ($diff < 604800) {
                                                $timeAgo = floor($diff / 86400) . ' days ago';
                                            } else {
                                                $timeAgo = date('M j, Y', $timestamp);
                                            }
                                            ?>
                                            <div class="time-group">
                                                <div class="primary"><?php echo $timeAgo; ?></div>
                                                <div class="secondary"><?php echo date('H:i', $timestamp); ?></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <style>
                        .shipments-section {
                            padding: 0 1rem;
                        }

                        .section-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 1.5rem;
                            flex-wrap: wrap;
                            gap: 1rem;
                        }

                        .section-title {
                            display: flex;
                            flex-direction: column;
                            gap: 0.25rem;
                        }

                        .section-title h2 {
                            font-size: 1.5rem;
                            color: var(--text);
                            margin: 0;
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                        }

                        .section-subtitle {
                            font-size: 0.9rem;
                            color: var(--muted);
                        }

                        .section-actions {
                            display: flex;
                            gap: 0.75rem;
                        }

                        .table-responsive {
                            background: var(--card);
                            border-radius: 12px;
                            overflow: hidden;
                            margin-bottom: 2rem;
                        }

                        .shipments-table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        .shipments-table th,
                        .shipments-table td {
                            padding: 1rem;
                            text-align: left;
                            border-bottom: 1px solid var(--border);
                        }

                        .shipments-table th {
                            background: var(--background);
                            font-weight: 500;
                            font-size: 0.9rem;
                            color: var(--muted);
                        }

                        .shipments-table td {
                            transition: background-color 0.2s ease;
                        }

                        .shipments-table tr:hover td {
                            background: var(--background);
                        }

                        .identifier-group {
                            display: flex;
                            flex-direction: column;
                            gap: 0.25rem;
                        }

                        .identifier-group .primary {
                            font-weight: 500;
                            color: var(--text);
                        }

                        .identifier-group .secondary {
                            font-size: 0.85rem;
                            color: var(--muted);
                        }

                        .metrics-grid {
                            display: grid;
                            gap: 0.35rem;
                        }

                        .metrics-item {
                            display: flex;
                            justify-content: space-between;
                            gap: 1rem;
                            font-size: 0.9rem;
                        }

                        .metrics-label {
                            color: var(--muted);
                        }

                        .metrics-value {
                            font-weight: 500;
                        }

                        .empty {
                            color: var(--muted);
                            font-size: 0.9rem;
                        }

                        .amount {
                            font-weight: 500;
                            color: var(--accent);
                        }

                        .amount .currency {
                            opacity: 0.7;
                        }

                        .time-group {
                            display: flex;
                            flex-direction: column;
                            gap: 0.25rem;
                        }

                        .time-group .primary {
                            font-weight: 500;
                        }

                        .time-group .secondary {
                            font-size: 0.85rem;
                            color: var(--muted);
                        }

                        @media (max-width: 1200px) {
                            .shipments-table th span {
                                display: none;
                            }

                            .shipments-table th i {
                                margin: 0;
                                font-size: 1.1rem;
                            }
                        }

                        @media (max-width: 768px) {
                            .shipments-section {
                                padding: 0;
                            }

                            .section-header {
                                padding: 0 1rem;
                            }

                            .table-responsive {
                                border-radius: 0;
                            }

                            .shipments-table thead {
                                display: none;
                            }

                            .shipments-table tr {
                                display: block;
                                padding: 1rem;
                                border-bottom: 1px solid var(--border);
                            }

                            .shipments-table td {
                                display: block;
                                padding: 0.5rem 0;
                                border: none;
                                text-align: right;
                            }

                            .shipments-table td::before {
                                content: attr(data-label);
                                float: left;
                                font-weight: 600;
                                color: var(--muted);
                            }

                            .metrics-grid,
                            .identifier-group,
                            .time-group {
                                text-align: left;
                            }
                        }
                    </style>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="pagination" role="navigation" aria-label="Shipments pagination">
                            <div class="pagination-grid">
                                <!-- Previous Page -->
                                <div class="pagination-prev">
                                    <?php if ($page > 1): ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                                            class="pagination-link" rel="prev">
                                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                                            <span>Previous</span>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <!-- Page Numbers -->
                                <div class="pagination-numbers">
                                    <?php
                                    $range = 2;
                                    $showLeftDots = $page - $range > 1;
                                    $showRightDots = $page + $range < $totalPages;

                                    if ($showLeftDots) {
                                        echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '" 
                                                 class="pagination-number">1</a>';
                                        echo '<span class="pagination-dots">...</span>';
                                    }

                                    for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++): ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                                            class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>"
                                            <?php echo $i === $page ? 'aria-current="page"' : ''; ?>>
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor;

                                    if ($showRightDots) {
                                        echo '<span class="pagination-dots">...</span>';
                                        echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $totalPages])) . '" 
                                                 class="pagination-number">' . $totalPages . '</a>';
                                    }
                                    ?>
                                </div>

                                <!-- Next Page -->
                                <div class="pagination-next">
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                                            class="pagination-link" rel="next">
                                            <span>Next</span>
                                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="pagination-info">
                                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                                <span class="bullet">•</span>
                                Showing <?php echo min($perPage, $totalShipments - $offset); ?> of <?php echo number_format($totalShipments); ?> shipments
                            </div>
                        </nav>

                        <style>
                            .pagination {
                                margin: 2rem 0;
                                text-align: center;
                            }

                            .pagination-grid {
                                display: grid;
                                grid-template-columns: 1fr auto 1fr;
                                gap: 1rem;
                                align-items: center;
                                margin-bottom: 1rem;
                            }

                            .pagination-prev {
                                justify-self: end;
                            }

                            .pagination-next {
                                justify-self: start;
                            }

                            .pagination-numbers {
                                display: flex;
                                gap: 0.5rem;
                                align-items: center;
                                justify-content: center;
                                flex-wrap: wrap;
                            }

                            .pagination-number {
                                min-width: 36px;
                                height: 36px;
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                padding: 0 0.5rem;
                                border-radius: 6px;
                                background: var(--card);
                                color: var(--text);
                                text-decoration: none;
                                transition: all 0.2s ease;
                            }

                            .pagination-number:hover {
                                background: var(--accent-transparent);
                                color: var(--accent);
                            }

                            .pagination-number.active {
                                background: var(--accent);
                                color: white;
                                font-weight: 500;
                            }

                            .pagination-dots {
                                color: var(--muted);
                                padding: 0 0.5rem;
                            }

                            .pagination-link {
                                display: inline-flex;
                                align-items: center;
                                gap: 0.5rem;
                                padding: 0.5rem 1rem;
                                border-radius: 6px;
                                background: var(--card);
                                color: var(--text);
                                text-decoration: none;
                                transition: all 0.2s ease;
                            }

                            .pagination-link:hover {
                                background: var(--accent-transparent);
                                color: var(--accent);
                            }

                            .pagination-info {
                                color: var(--muted);
                                font-size: 0.9rem;
                            }

                            .bullet {
                                display: inline-block;
                                margin: 0 0.5rem;
                                opacity: 0.5;
                            }

                            @media (max-width: 768px) {
                                .pagination {
                                    margin: 1rem;
                                }

                                .pagination-grid {
                                    grid-template-columns: 1fr;
                                    gap: 0.5rem;
                                }

                                .pagination-prev,
                                .pagination-next {
                                    justify-self: center;
                                }

                                .pagination-link {
                                    width: 100%;
                                    justify-content: center;
                                }
                            }
                        </style>
                    <?php endif; ?>
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
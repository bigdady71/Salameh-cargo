<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/header.php';

$query = '';
$shipments = [];
$searched = false;

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $query = trim($_GET['query']);
    $searched = true;

    // Search across multiple fields using prepared statements
    $stmt = $pdo->prepare('
        SELECT 
            s.shipment_id, 
            s.tracking_number,
            s.container_number,
            s.bl_number,
            s.shipping_code,
            s.status,
            s.cbm,
            s.cartons,
            s.weight,
            s.gross_weight,
            s.total_amount,
            s.created_at,
            s.updated_at,
            u.full_name,
            u.phone,
            COALESCE(ss.source_site, "Database") as source,
            ss.scrape_time
        FROM shipments s 
        LEFT JOIN users u ON s.user_id = u.user_id 
        LEFT JOIN (
            SELECT shipment_id, source_site, scrape_time,
                   ROW_NUMBER() OVER (PARTITION BY shipment_id ORDER BY scrape_time DESC) as rn
            FROM shipment_scrapes
        ) ss ON ss.shipment_id = s.shipment_id AND ss.rn = 1
        WHERE s.tracking_number = ? 
           OR s.container_number = ? 
           OR s.bl_number = ?
           OR u.phone = ? 
           OR u.full_name LIKE ?
           OR s.shipping_code = ?
           OR u.shipping_code = ?
        ORDER BY s.created_at DESC
        LIMIT 50
    ');

    $likeQuery = '%' . $query . '%';
    $stmt->execute([$query, $query, $query, $query, $likeQuery, $query, $query]);
    $shipments = $stmt->fetchAll();
}
?>

<main>
    <div class="container">
        <!-- Hero Section with Search -->
        <section class="hero">
            <div class="hero__content">
                <h1 class="hero__title">Track Your Shipment</h1>
                <p class="hero__subtitle">Enter your tracking number, container number, BL number, phone, or name to track your shipment in real-time.</p>

                <div class="search-container">
                    <div class="search-card">
                        <form method="GET" action="track.php" class="search-form" role="search">
                            <div class="form-group">
                                <label for="query" class="search-label">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                    Search by any identifier
                                </label>
                                <input type="text"
                                    id="query"
                                    name="query"
                                    required
                                    autocomplete="off"
                                    spellcheck="false"
                                    aria-label="Enter tracking number, container, shipping code, or other identifier"
                                    placeholder="Enter tracking number, container, BL, shipping code, phone, or name"
                                    value="<?php echo htmlspecialchars($query); ?>"
                                    class="search-input">
                                <div class="search-examples">
                                    <span class="example-chip">TR123456</span>
                                    <span class="example-chip">MSKU1234567</span>
                                    <span class="example-chip">ZAHER05</span>
                                    <span class="example-chip">+961XXXXXXXX</span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary search-button">
                                <i class="fas fa-search" aria-hidden="true"></i>
                                Track Shipment
                            </button>
                        </form>
                    </div>
                </div>

                <style>
                    .search-container {
                        max-width: 700px;
                        margin: 3rem auto;
                    }

                    .search-card {
                        background: var(--card);
                        border-radius: 12px;
                        padding: 2rem;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    }

                    .search-label {
                        text-align: center;
                        display: block;
                        margin-bottom: 1rem;
                        font-size: 1.1rem;
                        color: var(--text);
                    }

                    .search-input {
                        width: 100%;
                        text-align: center;
                        font-size: 1.1rem;
                        padding: 1rem;
                        border: 2px solid var(--border);
                        border-radius: 8px;
                        background: var(--background);
                        color: var(--text);
                        transition: all 0.3s ease;
                    }

                    .search-input:focus {
                        border-color: var(--accent);
                        box-shadow: 0 0 0 2px var(--accent-transparent);
                        outline: none;
                    }

                    .search-examples {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 0.5rem;
                        justify-content: center;
                        margin-top: 1rem;
                    }

                    .example-chip {
                        font-size: 0.85rem;
                        padding: 0.25rem 0.75rem;
                        background: var(--background);
                        border-radius: 100px;
                        color: var(--muted);
                    }

                    .search-button {
                        width: 100%;
                        padding: 1rem;
                        font-size: 1.1rem;
                        margin-top: 1.5rem;
                        border-radius: 8px;
                        transition: transform 0.2s ease;
                    }

                    .search-button:hover {
                        transform: translateY(-1px);
                    }

                    @media (max-width: 768px) {
                        .search-card {
                            padding: 1.5rem;
                            margin: 1rem;
                        }

                        .search-input {
                            font-size: 1rem;
                            padding: 0.75rem;
                        }

                        .example-chip {
                            font-size: 0.75rem;
                        }
                    }
                </style>
            </div>
        </section>

        <?php if ($searched): ?>
            <!-- Results Section -->
            <section class="cards" style="padding: 3rem 0;">
                <div class="container">
                    <?php if ($shipments): ?>
                        <div style="margin-bottom: 2rem;">
                            <h2 style="color: var(--text); margin-bottom: 0.5rem;">
                                <i class="fas fa-ship"></i>
                                Search Results
                            </h2>
                            <p style="color: var(--muted);">
                                Found <?php echo count($shipments); ?> shipment<?php echo count($shipments) !== 1 ? 's' : ''; ?> matching "<strong><?php echo htmlspecialchars($query); ?></strong>"
                            </p>
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
                                        <th data-label="Customer">
                                            <i class="fas fa-user" aria-hidden="true"></i>
                                            <span>Customer & Code</span>
                                        </th>
                                        <th data-label="Contact">
                                            <i class="fas fa-phone" aria-hidden="true"></i>
                                            <span>Contact</span>
                                        </th>
                                        <th data-label="Status">
                                            <i class="fas fa-flag" aria-hidden="true"></i>
                                            <span>Status</span>
                                        </th>
                                        <th data-label="Metrics">
                                            <i class="fas fa-weight-hanging" aria-hidden="true"></i>
                                            <span>Metrics</span>
                                        </th>
                                        <th data-label="Updated">
                                            <i class="fas fa-clock" aria-hidden="true"></i>
                                            <span>Last Updated</span>
                                        </th>
                                        <th data-label="Source">
                                            <i class="fas fa-database" aria-hidden="true"></i>
                                            <span>Source</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <style>
                                        .table-responsive {
                                            width: 100%;
                                            overflow-x: auto;
                                            -webkit-overflow-scrolling: touch;
                                            margin-bottom: 1rem;
                                            background:
                                                linear-gradient(to right, var(--background) 30%, rgba(255, 255, 255, 0)),
                                                linear-gradient(to right, rgba(255, 255, 255, 0), var(--background) 70%) 0 100%,
                                                radial-gradient(farthest-side at 0% 50%, rgba(0, 0, 0, 0.2), transparent),
                                                radial-gradient(farthest-side at 100% 50%, rgba(0, 0, 0, 0.2), transparent) 0 100%;
                                            background-repeat: no-repeat;
                                            background-size: 40px 100%, 40px 100%, 14px 100%, 14px 100%;
                                            background-position: 0 0, 100% 0, 0 0, 100% 0;
                                            background-attachment: local, local, scroll, scroll;
                                        }

                                        .shipments-table {
                                            width: 100%;
                                            border-collapse: separate;
                                            border-spacing: 0;
                                        }

                                        .shipments-table th {
                                            background: var(--card);
                                            color: var(--text);
                                            font-weight: 600;
                                            text-transform: uppercase;
                                            font-size: 0.8rem;
                                            letter-spacing: 0.5px;
                                            padding: 1rem;
                                            text-align: left;
                                            border-bottom: 2px solid var(--border);
                                        }

                                        .shipments-table td {
                                            padding: 1rem;
                                            border-bottom: 1px solid var(--border);
                                            background: var(--background);
                                        }

                                        .shipments-table tr:hover td {
                                            background: var(--card);
                                        }

                                        .shipments-table th i,
                                        .shipments-table td i {
                                            margin-right: 0.5rem;
                                            color: var(--accent);
                                        }

                                        .source-badge {
                                            display: inline-block;
                                            padding: 0.25rem 0.75rem;
                                            border-radius: 100px;
                                            font-size: 0.8rem;
                                            font-weight: 500;
                                            background: var(--card);
                                            color: var(--text);
                                        }

                                        .source-badge.live {
                                            background: var(--accent-transparent);
                                            color: var(--accent);
                                        }

                                        .metrics-grid {
                                            display: grid;
                                            gap: 0.5rem;
                                            font-size: 0.85rem;
                                        }

                                        .metrics-item {
                                            display: flex;
                                            justify-content: space-between;
                                        }

                                        .metrics-label {
                                            color: var(--muted);
                                        }

                                        .metrics-value {
                                            font-weight: 500;
                                        }

                                        @media (max-width: 1200px) {
                                            .shipments-table th span {
                                                display: none;
                                            }

                                            .shipments-table th i {
                                                margin: 0;
                                                font-size: 1.1rem;
                                            }

                                            .shipments-table td {
                                                font-size: 0.9rem;
                                            }
                                        }

                                        @media (max-width: 768px) {
                                            .table-responsive {
                                                background: none;
                                            }

                                            .shipments-table {
                                                border: 0;
                                            }

                                            .shipments-table thead {
                                                display: none;
                                            }

                                            .shipments-table tr {
                                                display: block;
                                                margin-bottom: 1rem;
                                                background: var(--card);
                                                border-radius: 8px;
                                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                            }

                                            .shipments-table td {
                                                display: block;
                                                text-align: right;
                                                padding: 0.75rem 1rem;
                                                border-bottom: 1px solid var(--border);
                                            }

                                            .shipments-table td:last-child {
                                                border-bottom: 0;
                                            }

                                            .shipments-table td::before {
                                                content: attr(data-label);
                                                float: left;
                                                font-weight: 600;
                                                text-transform: uppercase;
                                                font-size: 0.75rem;
                                                color: var(--muted);
                                            }
                                        }
                                    </style>
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
                                            <td data-label="Customer">
                                                <div class="identifier-group">
                                                    <div class="primary">
                                                        <?php echo $shipment['full_name'] ? htmlspecialchars($shipment['full_name']) : 'Unknown'; ?>
                                                    </div>
                                                    <?php if ($shipment['shipping_code']): ?>
                                                        <div class="secondary">
                                                            Code: <?php echo htmlspecialchars($shipment['shipping_code']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td data-label="Contact">
                                                <?php if ($shipment['phone']): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($shipment['phone']); ?>"
                                                        class="phone-link">
                                                        <i class="fas fa-phone-alt" aria-hidden="true"></i>
                                                        <?php echo htmlspecialchars($shipment['phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <div class="empty">No Phone</div>
                                                <?php endif; ?>
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
                                                    <?php if ($shipment['weight']): ?>
                                                        <div class="metrics-item">
                                                            <span class="metrics-label">Weight:</span>
                                                            <span class="metrics-value"><?php echo number_format($shipment['weight'], 2); ?> kg</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($shipment['total_amount']): ?>
                                                        <div class="metrics-item">
                                                            <span class="metrics-label">Amount:</span>
                                                            <span class="metrics-value">$<?php echo number_format($shipment['total_amount'], 2); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td data-label="Updated">
                                                <?php
                                                $updatedAt = $shipment['scrape_time'] ?: ($shipment['updated_at'] ?: $shipment['created_at']);
                                                $timestamp = strtotime($updatedAt);
                                                $now = time();
                                                $diff = $now - $timestamp;

                                                if ($diff < 3600) { // Less than 1 hour
                                                    $timeAgo = floor($diff / 60) . ' minutes ago';
                                                } elseif ($diff < 86400) { // Less than 24 hours
                                                    $timeAgo = floor($diff / 3600) . ' hours ago';
                                                } elseif ($diff < 604800) { // Less than 7 days
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
                                            <td data-label="Source">
                                                <?php
                                                $source = $shipment['source'] ?: 'Database';
                                                $isLive = $source !== 'Database';
                                                ?>
                                                <span class="source-badge <?php echo $isLive ? 'live' : ''; ?>">
                                                    <?php if ($isLive): ?>
                                                        <i class="fas fa-satellite-dish" aria-hidden="true"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-database" aria-hidden="true"></i>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($source); ?>
                                                </span>
                                            </td>
                                        </tr>

                                        <style>
                                            .identifier-group {
                                                display: flex;
                                                flex-direction: column;
                                                gap: 0.25rem;
                                            }

                                            .identifier-group .primary {
                                                font-weight: 500;
                                            }

                                            .identifier-group .secondary {
                                                font-size: 0.85rem;
                                                color: var(--muted);
                                            }

                                            .empty {
                                                color: var(--muted);
                                                font-size: 0.85rem;
                                            }

                                            .phone-link {
                                                color: var(--accent);
                                                text-decoration: none;
                                                display: inline-flex;
                                                align-items: center;
                                                gap: 0.5rem;
                                            }

                                            .phone-link:hover {
                                                text-decoration: underline;
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

                                            @media (max-width: 768px) {

                                                .identifier-group,
                                                .metrics-grid,
                                                .time-group {
                                                    text-align: left;
                                                    margin-left: 1rem;
                                                }
                                            }
                                        </style>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Section -->
                        <div class="results-summary">
                            <?php
                            $totalCBM = 0;
                            $totalCartons = 0;
                            $totalWeight = 0;
                            $totalAmount = 0;
                            $statusCounts = [];

                            foreach ($shipments as $shipment) {
                                $status = $shipment['status'];
                                $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
                                $totalCBM += floatval($shipment['cbm']);
                                $totalCartons += intval($shipment['cartons']);
                                $totalWeight += floatval($shipment['weight']);
                                $totalAmount += floatval($shipment['total_amount']);
                            }
                            ?>

                            <div class="summary-grid">
                                <div class="summary-card primary">
                                    <div class="summary-icon">
                                        <i class="fas fa-cube" aria-hidden="true"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-value"><?php echo count($shipments); ?></div>
                                        <div class="summary-label">Total Shipments</div>
                                    </div>
                                </div>

                                <?php if ($totalCBM > 0): ?>
                                    <div class="summary-card">
                                        <div class="summary-icon">
                                            <i class="fas fa-box" aria-hidden="true"></i>
                                        </div>
                                        <div class="summary-content">
                                            <div class="summary-value"><?php echo number_format($totalCBM, 2); ?></div>
                                            <div class="summary-label">Total CBM</div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($totalCartons > 0): ?>
                                    <div class="summary-card">
                                        <div class="summary-icon">
                                            <i class="fas fa-boxes" aria-hidden="true"></i>
                                        </div>
                                        <div class="summary-content">
                                            <div class="summary-value"><?php echo number_format($totalCartons); ?></div>
                                            <div class="summary-label">Total Cartons</div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($totalWeight > 0): ?>
                                    <div class="summary-card">
                                        <div class="summary-icon">
                                            <i class="fas fa-weight-hanging" aria-hidden="true"></i>
                                        </div>
                                        <div class="summary-content">
                                            <div class="summary-value"><?php echo number_format($totalWeight, 2); ?></div>
                                            <div class="summary-label">Total Weight (kg)</div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($totalAmount > 0): ?>
                                    <div class="summary-card">
                                        <div class="summary-icon">
                                            <i class="fas fa-dollar-sign" aria-hidden="true"></i>
                                        </div>
                                        <div class="summary-content">
                                            <div class="summary-value">$<?php echo number_format($totalAmount, 2); ?></div>
                                            <div class="summary-label">Total Amount</div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (count($statusCounts) > 0): ?>
                                <div class="status-summary">
                                    <h3>Status Breakdown</h3>
                                    <div class="status-grid">
                                        <?php foreach ($statusCounts as $status => $count): ?>
                                            <div class="status-card">
                                                <span class="status-badge status-<?php echo strtolower(str_replace([' ', '/', '&'], ['-', '-', 'and'], $status)); ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                                <span class="status-count"><?php echo $count; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <style>
                                .results-summary {
                                    margin-top: 2rem;
                                    padding: 2rem;
                                    background: var(--card);
                                    border-radius: 12px;
                                }

                                .summary-grid {
                                    display: grid;
                                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                                    gap: 1.5rem;
                                    margin-bottom: 2rem;
                                }

                                .summary-card {
                                    display: flex;
                                    align-items: center;
                                    gap: 1rem;
                                    padding: 1.5rem;
                                    background: var(--background);
                                    border-radius: 8px;
                                    transition: transform 0.2s ease;
                                }

                                .summary-card:hover {
                                    transform: translateY(-2px);
                                }

                                .summary-card.primary {
                                    background: var(--accent-transparent);
                                }

                                .summary-icon {
                                    width: 48px;
                                    height: 48px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    background: var(--card);
                                    border-radius: 12px;
                                    font-size: 1.5rem;
                                }

                                .primary .summary-icon {
                                    background: var(--accent);
                                    color: white;
                                }

                                .summary-content {
                                    flex: 1;
                                }

                                .summary-value {
                                    font-size: 1.5rem;
                                    font-weight: 600;
                                    line-height: 1.2;
                                }

                                .summary-label {
                                    font-size: 0.9rem;
                                    color: var(--muted);
                                }

                                .status-summary {
                                    margin-top: 2rem;
                                    padding-top: 2rem;
                                    border-top: 1px solid var(--border);
                                }

                                .status-summary h3 {
                                    margin-bottom: 1rem;
                                    color: var(--text);
                                }

                                .status-grid {
                                    display: flex;
                                    flex-wrap: wrap;
                                    gap: 1rem;
                                }

                                .status-card {
                                    display: flex;
                                    align-items: center;
                                    gap: 0.75rem;
                                    padding: 0.75rem 1rem;
                                    background: var(--background);
                                    border-radius: 8px;
                                }

                                .status-count {
                                    font-weight: 600;
                                    color: var(--text);
                                }

                                @media (max-width: 768px) {
                                    .results-summary {
                                        padding: 1rem;
                                        margin: 1rem;
                                    }

                                    .summary-card {
                                        padding: 1rem;
                                    }

                                    .summary-value {
                                        font-size: 1.25rem;
                                    }
                                }
                            </style>
                        </div>

                    <?php else: ?>
                        <!-- No Results -->
                        <div class="hero" style="min-height: 40vh;">
                            <div class="hero__content">
                                <div class="no-results">
                                    <div class="no-results__icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <h2 class="no-results__title">No Shipments Found</h2>
                                    <p class="no-results__message">
                                        No shipments match "<?php echo htmlspecialchars($query); ?>". Please verify your:
                                    </p>
                                    <ul class="no-results__tips">
                                        <li>Tracking number</li>
                                        <li>Container number</li>
                                        <li>Bill of lading number</li>
                                        <li>Phone number</li>
                                        <li>Shipping code</li>
                                    </ul>
                                    <style>
                                        .no-results {
                                            text-align: center;
                                            padding: 2rem;
                                        }

                                        .no-results__icon {
                                            background: var(--card);
                                            border-radius: 50%;
                                            width: 100px;
                                            height: 100px;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            margin: 0 auto 2rem;
                                            font-size: 2rem;
                                            color: var(--muted);
                                        }

                                        .no-results__title {
                                            color: var(--text);
                                            margin-bottom: 1rem;
                                        }

                                        .no-results__message {
                                            color: var(--muted);
                                            margin-bottom: 1rem;
                                        }

                                        .no-results__tips {
                                            list-style: none;
                                            padding: 0;
                                            margin: 0;
                                            color: var(--muted);
                                        }

                                        .no-results__tips li {
                                            display: inline-block;
                                            margin: 0.25rem 0.5rem;
                                        }
                                    </style>
                                    <i class="fas fa-search" style="font-size: 2.5rem; color: var(--muted);"></i>
                                </div>
                                <h2 style="color: var(--text); font-size: 1.75rem; margin-bottom: 1rem;">No Shipments Found</h2>
                                <p style="color: var(--muted); font-size: 1.1rem; max-width: 600px; margin: 0 auto 2rem;">
                                    We couldn't find any shipments matching "<strong><?php echo htmlspecialchars($query); ?></strong>".
                                    Please check your search term and try again.
                                </p>

                                <div style="margin: 2rem 0;">
                                    <h4 style="color: var(--accent); margin-bottom: 1rem;">Search Tips:</h4>
                                    <ul style="color: var(--muted); list-style: none; padding: 0; line-height: 2;">
                                        <li><i class="fas fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i> Try your complete tracking number (e.g., TR123456)</li>
                                        <li><i class="fas fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i> Use your container number if available</li>
                                        <li><i class="fas fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i> Search by your registered phone number</li>
                                        <li><i class="fas fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i> Try your full name as registered</li>
                                    </ul>
                                </div>

                                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                    <a href="/public/track.php" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                        Try Another Search
                                    </a>
                                    <a href="https://wa.me/96171123456?text=I%20need%20help%20tracking%20my%20shipment%20with%20identifier:%20<?php echo urlencode($query); ?>"
                                        class="btn btn-secondary" target="_blank">
                                        <i class="fab fa-whatsapp"></i>
                                        Get Help via WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Help Section -->
        <?php if (!$searched): ?>
            <section class="why">
                <div class="container">
                    <div class="why__header">
                        <h2 class="why__title">What You Can Search For</h2>
                    </div>
                    <div class="why__grid">
                        <div class="why__item">
                            <div class="why__icon">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <h3 class="why__item-title">Tracking Number</h3>
                            <p class="why__item-desc">
                                Use your unique tracking number provided when your shipment was booked. Usually starts with letters followed by numbers (e.g., TR123456).
                            </p>
                        </div>

                        <div class="why__item">
                            <div class="why__icon">
                                <i class="fas fa-container"></i>
                            </div>
                            <h3 class="why__item-title">Container Number</h3>
                            <p class="why__item-desc">
                                Search using your container number if available. This is typically a combination of letters and numbers assigned by the shipping line.
                            </p>
                        </div>

                        <div class="why__item">
                            <div class="why__icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3 class="why__item-title">Personal Information</h3>
                            <p class="why__item-desc">
                                Use your registered phone number or full name to find all shipments associated with your account.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<script>
    // Auto-focus on search input
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('query');
        if (searchInput && !searchInput.value) {
            searchInput.focus();
        }
    });

    // Enhance table responsiveness
    if (window.innerWidth <= 768) {
        document.querySelectorAll('.shipments-table th, .shipments-table td').forEach(cell => {
            cell.style.fontSize = '0.8rem';
            cell.style.padding = '0.5rem 0.25rem';
        });
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
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
        SELECT s.*, u.full_name, u.phone 
        FROM shipments s 
        LEFT JOIN users u ON s.user_id = u.user_id 
        WHERE s.tracking_number = ? 
           OR s.container_number = ? 
           OR s.bl_number = ?
           OR u.phone = ? 
           OR u.full_name LIKE ?
           OR s.shipping_code = ?
        ORDER BY s.created_at DESC
        LIMIT 50
    ');
    
    $likeQuery = '%' . $query . '%';
    $stmt->execute([$query, $query, $query, $query, $likeQuery, $query]);
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
                
                <div style="max-width: 600px; margin: 3rem auto;">
                    <div class="card" style="padding: 2rem;">
                        <form method="GET" action="track.php" class="search-form">
                            <div class="form-group">
                                <label for="query" style="text-align: center; display: block; margin-bottom: 1rem; font-size: 1.1rem;">
                                    <i class="fas fa-search"></i>
                                    Search by any identifier
                                </label>
                                <input type="text" 
                                       id="query" 
                                       name="query" 
                                       required 
                                       placeholder="Enter tracking number, container, BL, phone, or customer name"
                                       value="<?php echo htmlspecialchars($query); ?>"
                                       style="text-align: center; font-size: 1.1rem; padding: 1rem;">
                                <small style="text-align: center; display: block; margin-top: 0.5rem;">
                                    Examples: TR123456, MSKU1234567, +961XXXXXXXX, John Doe
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                                <i class="fas fa-search"></i>
                                Track Shipment
                            </button>
                        </form>
                    </div>
                </div>
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

                        <div class="table-container">
                            <table class="shipments-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag"></i> Tracking Number</th>
                                        <th><i class="fas fa-container"></i> Container</th>
                                        <th><i class="fas fa-user"></i> Customer</th>
                                        <th><i class="fas fa-phone"></i> Phone</th>
                                        <th><i class="fas fa-flag"></i> Status</th>
                                        <th><i class="fas fa-weight-hanging"></i> Details</th>
                                        <th><i class="fas fa-clock"></i> Last Updated</th>
                                        <th><i class="fas fa-database"></i> Source</th>
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
                                                <?php echo $shipment['full_name'] ? htmlspecialchars($shipment['full_name']) : '<span style="color: var(--muted);">Unknown</span>'; ?>
                                                <?php if ($shipment['shipping_code']): ?>
                                                    <br><small style="color: var(--muted);">Code: <?php echo htmlspecialchars($shipment['shipping_code']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($shipment['phone']): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($shipment['phone']); ?>" 
                                                       style="color: var(--accent);">
                                                        <?php echo htmlspecialchars($shipment['phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color: var(--muted);">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower(str_replace([' ', '/', '&'], ['-', '-', 'and'], $shipment['status'])); ?>">
                                                    <?php echo htmlspecialchars($shipment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="font-size: 0.85rem; color: var(--muted);">
                                                    <?php if ($shipment['cbm']): ?>
                                                        <div>CBM: <?php echo htmlspecialchars($shipment['cbm']); ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($shipment['cartons']): ?>
                                                        <div>Cartons: <?php echo htmlspecialchars($shipment['cartons']); ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($shipment['weight']): ?>
                                                        <div>Weight: <?php echo htmlspecialchars($shipment['weight']); ?> kg</div>
                                                    <?php endif; ?>
                                                    <?php if ($shipment['total_amount']): ?>
                                                        <div>Amount: $<?php echo htmlspecialchars($shipment['total_amount']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $updatedAt = $shipment['updated_at'] ? $shipment['updated_at'] : $shipment['created_at'];
                                                echo date('M j, Y', strtotime($updatedAt)); 
                                                ?>
                                                <br>
                                                <small style="color: var(--muted);">
                                                    <?php echo date('H:i', strtotime($updatedAt)); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span style="background: var(--card); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                                    Database
                                                </span>
                                                <?php if ($shipment['source']): ?>
                                                    <br><small style="color: var(--muted);"><?php echo htmlspecialchars($shipment['source']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Section -->
                        <div class="summary" style="margin-top: 2rem;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <p><i class="fas fa-list" style="color: var(--accent);"></i> 
                                   <strong>Total Results:</strong> <?php echo count($shipments); ?></p>
                                
                                <?php 
                                $statusCounts = [];
                                foreach ($shipments as $shipment) {
                                    $status = $shipment['status'];
                                    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
                                }
                                ?>
                                
                                <?php foreach ($statusCounts as $status => $count): ?>
                                    <p><span class="status-badge status-<?php echo strtolower(str_replace([' ', '/', '&'], ['-', '-', 'and'], $status)); ?>" style="margin-right: 0.5rem;">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span> <?php echo $count; ?></p>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- No Results -->
                        <div class="hero" style="min-height: 40vh;">
                            <div class="hero__content">
                                <div style="background: var(--card); border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
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

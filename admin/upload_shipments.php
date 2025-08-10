<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
include __DIR__ . '/../includes/header.php';

$message = '';
$error = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a valid file.';
    } else {
        $file = $_FILES['file'];
        $user_id = $_POST['user_id'] ?? null;

        if (!$user_id) {
            $error = 'Please select a user.';
        } else {
            // Validate file type
            $allowed_types = ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($file['type'], $allowed_types) && !in_array($file_extension, ['csv', 'xlsx'])) {
                $error = 'Invalid file type. Please upload CSV or Excel files only.';
            } else {
                try {
                    $shipments_created = 0;
                    $shipments_failed = 0;

                    // Handle CSV file
                    if ($file_extension === 'csv' || $file['type'] === 'text/csv') {
                        $handle = fopen($file['tmp_name'], 'r');
                        if ($handle) {
                            // Skip header row
                            fgetcsv($handle);

                            while (($data = fgetcsv($handle)) !== false) {
                                if (count($data) >= 8) {
                                    $tracking = trim($data[0]);
                                    $container = trim($data[1]);
                                    $description = trim($data[2]);
                                    $cbm = floatval($data[3]);
                                    $cartons = intval($data[4]);
                                    $weight = floatval($data[5]);
                                    $gross_weight = floatval($data[6]);
                                    $amount = floatval($data[7]);

                                    if (!empty($tracking)) {
                                        try {
                                            $stmt = $pdo->prepare('INSERT INTO shipments (user_id, tracking_number, container_number, product_description, cbm, cartons, weight, gross_weight, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                                            $stmt->execute([$user_id, $tracking, $container, $description, $cbm, $cartons, $weight, $gross_weight, $amount, 'En Route']);
                                            $shipments_created++;
                                        } catch (PDOException $e) {
                                            $shipments_failed++;
                                        }
                                    }
                                }
                            }
                            fclose($handle);
                        }
                    } else {
                        // For Excel files, instruct to convert to CSV for now
                        $error = 'Excel files not yet supported. Please convert to CSV format.';
                    }

                    if ($shipments_created > 0) {
                        $message = "Successfully created $shipments_created shipments.";
                        if ($shipments_failed > 0) {
                            $message .= " Failed to create $shipments_failed shipments.";
                        }
                    }
                } catch (Exception $e) {
                    $error = 'Error processing file: ' . $e->getMessage();
                }
            }
        }
    }
}

// Get users for dropdown
$users = [];
try {
    $stmt = $pdo->prepare('SELECT user_id, full_name, phone FROM users ORDER BY full_name');
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading users: ' . $e->getMessage();
}
?>

<div class="container">
    <h1>Upload Shipments</h1>

    <?php if ($message): ?>
        <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="upload-form">
        <div class="form-group">
            <label for="user_id">Select User:</label>
            <select name="user_id" id="user_id" required>
                <option value="">Choose a user...</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>">
                        <?php echo htmlspecialchars($user['full_name'] . ' (' . $user['phone'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="file">Upload File (CSV):</label>
            <input type="file" name="file" id="file" accept=".csv,.xlsx" required>
            <small>Expected columns: Tracking, Container, Description, CBM, Cartons, Weight, GW, Amount</small>
        </div>

        <button type="submit" class="btn btn-primary">Upload Shipments</button>
    </form>

    <div class="help-section">
        <h3>CSV Format Instructions:</h3>
        <p>Your CSV file should have the following columns in order:</p>
        <ol>
            <li><strong>Tracking</strong> - Tracking number (required)</li>
            <li><strong>Container</strong> - Container number (optional)</li>
            <li><strong>Description</strong> - Product description (optional)</li>
            <li><strong>CBM</strong> - Cubic meters (numeric)</li>
            <li><strong>Cartons</strong> - Number of cartons (numeric)</li>
            <li><strong>Weight</strong> - Net weight (numeric)</li>
            <li><strong>GW</strong> - Gross weight (numeric)</li>
            <li><strong>Amount</strong> - Total amount (numeric)</li>
        </ol>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
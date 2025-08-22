<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$pageTitle = 'Upload Shipments';
include __DIR__ . '/../includes/admin-header.php';

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
                        // Handle Excel files
                        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                            if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
                                $error = 'PHPSpreadsheet not installed. Please run: composer require phpoffice/phpspreadsheet';
                                throw new Exception($error);
                            }
                            require_once __DIR__ . '/../vendor/autoload.php';
                        }

                        try {
                            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
                            $worksheet = $spreadsheet->getActiveSheet();
                            $rows = $worksheet->toArray();

                            // Skip header row
                            array_shift($rows);

                            // Begin transaction
                            $pdo->beginTransaction();

                            foreach ($rows as $row) {
                                if (count($row) >= 8) {
                                    $tracking = trim($row[0] ?? '');
                                    $container = trim($row[1] ?? '');
                                    $description = trim($row[2] ?? '');
                                    $cbm = floatval($row[3] ?? 0);
                                    $cartons = intval($row[4] ?? 0);
                                    $weight = floatval($row[5] ?? 0);
                                    $gross_weight = floatval($row[6] ?? 0);
                                    $amount = floatval($row[7] ?? 0);
                                    $shipping_code = trim($row[8] ?? ''); // Optional column

                                    if (!empty($tracking)) {
                                        try {
                                            $stmt = $pdo->prepare('
                                                INSERT INTO shipments (
                                                    user_id, tracking_number, container_number,
                                                    product_description, cbm, cartons,
                                                    weight, gross_weight, total_amount,
                                                    shipping_code, status
                                                ) VALUES (
                                                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                                                )
                                            ');
                                            $stmt->execute([
                                                $user_id,
                                                $tracking,
                                                $container,
                                                $description,
                                                $cbm,
                                                $cartons,
                                                $weight,
                                                $gross_weight,
                                                $amount,
                                                $shipping_code ?: null,
                                                'En Route'
                                            ]);
                                            $shipments_created++;
                                        } catch (PDOException $e) {
                                            if ($e->getCode() == 23000) { // Duplicate entry
                                                $shipments_failed++;
                                            } else {
                                                throw $e;
                                            }
                                        }
                                    }
                                }
                            }

                            // Commit transaction
                            $pdo->commit();
                        } catch (Exception $e) {
                            $pdo->rollBack();
                            throw $e;
                        }
                    }

                    // Log the import
                    $stmt = $pdo->prepare('
                        INSERT INTO logs (action_type, actor_id, details) 
                        VALUES (?, ?, ?)
                    ');
                    $stmt->execute([
                        'shipments_import',
                        -$_SESSION['admin_id'],
                        "Imported $shipments_created shipments ($shipments_failed failed) from {$file['name']}"
                    ]);

                    if ($shipments_created > 0) {
                        $message = "Successfully created $shipments_created shipments.";
                        if ($shipments_failed > 0) {
                            $message .= " Failed to create $shipments_failed shipments (duplicates or invalid data).";
                        }
                    }

                    // Remove the temporary file
                    @unlink($file['tmp_name']);
                } catch (Exception $e) {
                    $error = 'Error processing file: ' . $e->getMessage();
                }
            }
        }
    }
}

// Get users for dropdown with shipping code
$users = [];
try {
    $stmt = $pdo->prepare('
        SELECT user_id, full_name, phone, shipping_code 
        FROM users 
        ORDER BY full_name
    ');
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading users: ' . $e->getMessage();
}
?>

<div class="container">
    <h1>Upload Shipments</h1>

    <div class="card" style="margin-bottom: 2rem;">
        <h2>Instructions</h2>
        <p>Upload a CSV or Excel file with the following columns:</p>
        <table class="info-table">
            <thead>
                <tr>
                    <th>Column</th>
                    <th>Required</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tracking Number</td>
                    <td>Yes</td>
                    <td>Unique identifier for the shipment</td>
                </tr>
                <tr>
                    <td>Container Number</td>
                    <td>No</td>
                    <td>Container number if applicable</td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td>No</td>
                    <td>Product or shipment description</td>
                </tr>
                <tr>
                    <td>CBM</td>
                    <td>No</td>
                    <td>Cubic meters</td>
                </tr>
                <tr>
                    <td>Cartons</td>
                    <td>No</td>
                    <td>Number of cartons</td>
                </tr>
                <tr>
                    <td>Weight</td>
                    <td>No</td>
                    <td>Net weight in kg</td>
                </tr>
                <tr>
                    <td>Gross Weight</td>
                    <td>No</td>
                    <td>Gross weight in kg</td>
                </tr>
                <tr>
                    <td>Amount</td>
                    <td>No</td>
                    <td>Total amount</td>
                </tr>
                <tr>
                    <td>Shipping Code</td>
                    <td>No</td>
                    <td>Optional shipping code</td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if ($message): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
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

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
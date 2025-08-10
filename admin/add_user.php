<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Ensure user is logged in as admin
requireAdmin();

// Initialize variables
$error = '';
$success = '';
$formData = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'shipping_code' => '',
    'address' => '',
    'country' => '',
    'id_number' => ''
];

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid form submission.';
    } else {
        // Sanitize and validate input
        $formData = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
            'phone' => trim($_POST['phone'] ?? ''),
            'shipping_code' => trim($_POST['shipping_code'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'id_number' => trim($_POST['id_number'] ?? '')
        ];

        // Validation
        if (empty($formData['full_name'])) {
            $error = 'Full name is required.';
        } elseif (empty($formData['phone'])) {
            $error = 'Phone number is required.';
        } else {
            try {
                // Check for duplicate phone
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE phone = ?');
                $stmt->execute([$formData['phone']]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'This phone number is already registered.';
                } else {
                    // Check for duplicate shipping code if provided
                    if (!empty($formData['shipping_code'])) {
                        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE shipping_code = ?');
                        $stmt->execute([$formData['shipping_code']]);
                        if ($stmt->fetchColumn() > 0) {
                            $error = 'This shipping code is already in use.';
                        }
                    }

                    // If no errors, insert the user
                    if (empty($error)) {
                        $stmt = $pdo->prepare('
                            INSERT INTO users (
                                full_name, email, phone, shipping_code, 
                                address, country, id_number
                            ) VALUES (?, ?, ?, ?, ?, ?, ?)
                        ');
                        $stmt->execute([
                            $formData['full_name'],
                            $formData['email'] ?: null,
                            $formData['phone'],
                            $formData['shipping_code'] ?: null,
                            $formData['address'] ?: null,
                            $formData['country'] ?: null,
                            $formData['id_number'] ?: null
                        ]);

                        // Log the action
                        $userId = $pdo->lastInsertId();
                        $adminId = $_SESSION['admin_id'];
                        $stmt = $pdo->prepare('
                            INSERT INTO logs (action_type, actor_id, details) 
                            VALUES (?, ?, ?)
                        ');
                        $stmt->execute([
                            'user_created',
                            -$adminId, // Negative indicates admin
                            "Created user {$formData['full_name']} (ID: $userId)"
                        ]);

                        $success = 'User added successfully!';
                        // Clear form data after successful submission
                        $formData = array_fill_keys(array_keys($formData), '');
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error occurred. Please try again.';
                error_log('Error in add_user.php: ' . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add New User - Salameh Cargo</title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .error-message {
            color: #dc3545;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            background-color: rgba(220, 53, 69, 0.1);
        }

        .success-message {
            color: #28a745;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            background-color: rgba(40, 167, 69, 0.1);
        }

        button[type="submit"] {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background: #0056b3;
        }

        .required::after {
            content: " *";
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Add New User</h2>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label for="full_name" class="required">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($formData['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone" class="required">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>">
            </div>

            <div class="form-group">
                <label for="shipping_code">Shipping Code</label>
                <input type="text" id="shipping_code" name="shipping_code" value="<?php echo htmlspecialchars($formData['shipping_code']); ?>">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($formData['address']); ?>">
            </div>

            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($formData['country']); ?>">
            </div>

            <div class="form-group">
                <label for="id_number">ID Number</label>
                <input type="text" id="id_number" name="id_number" value="<?php echo htmlspecialchars($formData['id_number']); ?>">
            </div>

            <button type="submit">Add User</button>
        </form>
    </div>
</body>

</html>
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$error_message = '';
$MAX_ATTEMPTS = 5;
$LOCKOUT_DURATION = 30; // minutes

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Get admin details including security fields
    $stmt = $pdo->prepare('SELECT admin_id, password_hash, failed_attempts, locked_until, is_active FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Check if account is active
        if (!$admin['is_active']) {
            $error_message = 'Account is deactivated. Please contact a superadmin.';
        }
        // Check if account is locked
        elseif ($admin['locked_until'] && new DateTime($admin['locked_until']) > new DateTime()) {
            $error_message = 'Account is temporarily locked. Please try again later.';
        }
        // Verify password and handle attempts
        elseif (password_verify($password, $admin['password_hash'])) {
            // Reset failed attempts and update last login
            $stmt = $pdo->prepare('UPDATE admins SET failed_attempts = 0, locked_until = NULL, last_login_at = CURRENT_TIMESTAMP WHERE admin_id = ?');
            $stmt->execute([$admin['admin_id']]);

            // Start session
            $_SESSION['admin_id'] = $admin['admin_id'];
            header('Location: index.php');
            exit;
        } else {
            // Increment failed attempts
            $failed_attempts = $admin['failed_attempts'] + 1;

            // Lock account if max attempts reached
            $locked_until = $failed_attempts >= $MAX_ATTEMPTS ?
                (new DateTime())->modify("+{$LOCKOUT_DURATION} minutes")->format('Y-m-d H:i:s') :
                null;

            $stmt = $pdo->prepare('UPDATE admins SET failed_attempts = ?, locked_until = ? WHERE admin_id = ?');
            $stmt->execute([$failed_attempts, $locked_until, $admin['admin_id']]);

            if ($locked_until) {
                $error_message = 'Too many failed attempts. Account locked for ' . $LOCKOUT_DURATION . ' minutes.';
            } else {
                $remaining_attempts = $MAX_ATTEMPTS - $failed_attempts;
                $error_message = 'Invalid password. ' . $remaining_attempts . ' attempts remaining.';
            }
        }
    } else {
        // Generic message to avoid username enumeration
        $error_message = 'Invalid credentials';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Salameh Cargo</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            background-color: rgba(220, 53, 69, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>
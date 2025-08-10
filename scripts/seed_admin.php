<?php
require_once __DIR__ . '/../includes/db.php';

// Generate a secure random password if none provided
$password = $argv[1] ?? bin2hex(random_bytes(8));
$username = $argv[2] ?? 'admin';

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin already exists
    $stmt = $pdo->prepare('SELECT admin_id FROM admins WHERE username = ?');
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        echo "Admin user '$username' already exists.\n";
        exit(1);
    }

    // Insert new admin
    $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)');
    $stmt->execute([$username, $password_hash, 'superadmin']);

    echo "Admin user created successfully!\n";
    echo "Username: $username\n";
    echo "Password: $password\n";
    echo "Role: superadmin\n";
    echo "\nPlease change the password after first login.\n";
} catch (PDOException $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
    exit(1);
}

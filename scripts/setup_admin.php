<?php
require_once __DIR__ . '/../includes/db.php';

try {
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/admin_setup.sql');

    // Execute the SQL commands
    $pdo->exec($sql);

    echo "Admin user created successfully!\n";
    echo "Username: hsyn\n";
    echo "Password: tryu123\n";
} catch (PDOException $e) {
    echo "Error creating admin: " . $e->getMessage() . "\n";
    exit(1);
}

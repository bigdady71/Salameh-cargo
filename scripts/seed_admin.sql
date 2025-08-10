<?php
require __DIR__ . '/../includes/db.php'; // uses PDO $pdo
$username = 'admin';
$password = 'ChangeMe!123';
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO admins (username, password_hash, role) VALUES (?, ?, 'superadmin')");
$stmt->execute([$username, $hash]);
echo "Admin seeded. Username=admin, Password=$password\n";

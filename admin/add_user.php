<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE phone = ?');
    $stmt->execute([$phone]);
    if ($stmt->fetchColumn() > 0) {
        echo 'Phone number already exists.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (phone) VALUES (?)');
        $stmt->execute([$phone]);
        echo 'User added successfully.';
    }
}
?>

<form method='post'>
    <label for='phone'>Phone:</label>
    <input type='text' id='phone' name='phone' required>
    <button type='submit'>Add User</button>
</form>
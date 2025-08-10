<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare('SELECT admin_id, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        header('Location: index.php');
        exit;
    } else {
        echo 'Invalid credentials';
    }
}
?>

<form method='post'>
    <label for='username'>Username:</label>
    <input type='text' id='username' name='username' required>
    <label for='password'>Password:</label>
    <input type='password' id='password' name='password' required>
    <button type='submit'>Login</button>
</form>
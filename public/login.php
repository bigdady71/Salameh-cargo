<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['phone'])) {
        $phone = $_POST['phone'];
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE phone = ?');
        $stmt->execute([$phone]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            header('Location: dashboard.php');
            exit;
        } else {
            echo 'Phone number not found.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<form method='post'>
    <label for='phone'>Phone:</label>
    <input type='text' id='phone' name='phone' required>
    <button type='submit'>Send OTP</button>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
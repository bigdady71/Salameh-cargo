<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => false, // Set to true in production with HTTPS
    'use_strict_mode' => true,
]);

function requireUser()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: /public/login.php');
        exit;
    }
}

function requireAdmin()
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token)
{
    return hash_equals($_SESSION['csrf_token'], $token);
}

function logAction($actionType, $actorId, $relatedShipmentId = null, $details = null)
{
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO logs (action_type, actor_id, related_shipment_id, details) VALUES (?, ?, ?, ?)');
    $stmt->execute([$actionType, $actorId, $relatedShipmentId, $details]);
}

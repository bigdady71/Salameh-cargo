<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . urlp('admin/login.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="admin-mode">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Salameh Cargo</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Base Styles -->
<link rel="stylesheet" href="<?= asset('css/utilities.css') ?>?v=20250823">
<link rel="stylesheet" href="<?= asset('css/admin-base.css') ?>?v=20250823">

<?php
// Page-scoped stylesheets, set: $pageStyles = ['css/some-page.css']
if (!empty($pageStyles) && is_array($pageStyles)) {
    foreach ($pageStyles as $path) {
        echo '<link rel="stylesheet" href="' . asset($path) . '?v=20250823">' . PHP_EOL;
    }
}
?>

    <?php if (isset($_SESSION['component_styles'])): ?>
    <?php foreach ($_SESSION['component_styles'] as $component): ?>
    <link rel="stylesheet" href="<?= asset('css/components/' . $component . '.css') ?>?v=20250816">
    <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="admin-panel">
   <!-- Top Navbar  -->
<header class="admin-header">
  <div class="header-left">

    <nav class="topnav">
      <a href="<?= urlp('admin/index.php') ?>"
         class="<?= basename($_SERVER['PHP_SELF'])==='index.php' ? 'active' : '' ?>">Dashboard</a>
      <a href="<?= urlp('admin/shipments.php') ?>"
         class="<?= basename($_SERVER['PHP_SELF'])==='shipments.php' ? 'active' : '' ?>">Manage Shipments</a>
      <a href="<?= urlp('admin/upload_shipments.php') ?>"
         class="<?= basename($_SERVER['PHP_SELF'])==='upload_shipments.php' ? 'active' : '' ?>">Upload Shipments</a>
      <a href="<?= urlp('admin/add_user.php') ?>"
         class="<?= basename($_SERVER['PHP_SELF'])==='add_user.php' ? 'active' : '' ?>">Add User</a>
      <a href="<?= urlp('admin/automation.php') ?>"
         class="<?= basename($_SERVER['PHP_SELF'])==='automation.php' ? 'active' : '' ?>">Automation</a>
    </nav>
  </div>

  <div class="header-actions">
    <span class="user-welcome">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
    <a class="logout" href="<?= urlp('admin/logout.php') ?>" title="Logout">
      <i class="fas fa-sign-out-alt"></i>
    </a>
  </div>
</header>

<main class="admin-main">
  <div class="admin-content">

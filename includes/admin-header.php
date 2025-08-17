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
    <link rel="stylesheet" href="<?= asset('css/utilities.css') ?>?v=20250816">
    <link rel="stylesheet" href="<?= asset('css/admin-dashboard.css') ?>?v=20250816">

    <?php if (isset($_SESSION['component_styles'])): ?>
        <?php foreach ($_SESSION['component_styles'] as $component): ?>
            <link rel="stylesheet" href="<?= asset('css/components/' . $component . '.css') ?>?v=20250816">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="admin-panel">
    <div class="admin-layout">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <a href="<?= urlp('admin/index.php') ?>" class="sidebar-brand">
                <i class="fas fa-box"></i>
                <span>Salameh Admin</span>
            </a>

            <nav class="sidebar-nav">
                <ul class="nav-items">
                    <li class="nav-item">
                        <a href="<?= urlp('admin/index.php') ?>" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                            <i class="fas fa-home nav-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= urlp('admin/shipments.php') ?>" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'shipments.php' ? 'active' : '' ?>">
                            <i class="fas fa-ship nav-icon"></i>
                            <span>Shipments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= urlp('admin/upload_shipments.php') ?>" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'upload_shipments.php' ? 'active' : '' ?>">
                            <i class="fas fa-upload nav-icon"></i>
                            <span>Upload Shipments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= urlp('admin/add_user.php') ?>" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'add_user.php' ? 'active' : '' ?>">
                            <i class="fas fa-user-plus nav-icon"></i>
                            <span>Add User</span>
                        </a>
                    </li>
                    <a href="<?= urlp('admin/index.php') ?>" class="nav-item<?= ($_SERVER['PHP_SELF'] == '/admin/index.php' ? ' active' : '') ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    </li>
                    <li>
                        <a href="<?= urlp('admin/shipments.php') ?>" class="nav-item<?= ($_SERVER['PHP_SELF'] == '/admin/shipments.php' ? ' active' : '') ?>">
                            <i class="fas fa-shipping-fast"></i>
                            <span>Shipments</span>
                        </a>
                    </li>
                </ul>
    </div>

    <div class="nav-section">
        <h3 class="nav-title">Management</h3>
        <ul class="nav-items">
            <li>
                <a href="<?= urlp('admin/automation.php') ?>" class="nav-item<?= ($_SERVER['PHP_SELF'] == '/admin/automation.php' ? ' active' : '') ?>">
                    <i class="fas fa-robot"></i>
                    <span>Automation</span>
                </a>
            </li>
            <li>
                <a href="<?= urlp('admin/upload_shipments.php') ?>" class="nav-item<?= ($_SERVER['PHP_SELF'] == '/admin/upload_shipments.php' ? ' active' : '') ?>">
                    <i class="fas fa-upload"></i>
                    <span>Upload Shipments</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="nav-section">
        <h3 class="nav-title">Settings</h3>
        <ul class="nav-items">
            <li>
                <a href="<?= urlp('admin/add_user.php') ?>" class="nav-item<?= ($_SERVER['PHP_SELF'] == '/admin/add_user.php' ? ' active' : '') ?>">
                    <i class="fas fa-user-plus"></i>
                    <span>Add User</span>
                </a>
            </li>
            <li>
                <a href="<?= urlp('public/logout.php') ?>" class="nav-item text-error">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    </nav>
    </aside>

    <!-- Admin Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-content">
                <h1 class="page-title">
                    <?php
                    $current_page = basename($_SERVER['PHP_SELF'], '.php');
                    echo ucfirst(str_replace('_', ' ', $current_page));
                    ?>
                </h1>
                <div class="header-actions">
                    <span class="user-welcome">
                        Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
                    </span>
                </div>
            </div>
        </header>

        <div class="admin-content">
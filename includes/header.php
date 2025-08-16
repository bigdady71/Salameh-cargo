<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salameh Cargo - Shipment Tracking</title>

    <?php require_once __DIR__ . '/bootstrap.php'; ?>

    <link rel="preconnect" href="<?= APP_BASE ?>" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Base Styles -->
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>?v=20250816">
    <link rel="stylesheet" href="<?= asset('css/utilities.css') ?>?v=20250816">

    <!-- Components -->
    <link rel="stylesheet" href="<?= asset('css/components/navigation.css') ?>?v=20250816">

    <?php if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false): ?>
        <!-- Admin Styles -->
        <link rel="stylesheet" href="<?= asset('css/admin-dashboard.css') ?>?v=20250816">
        <?php if (isset($_SESSION['component_styles'])): ?>
            <!-- Component Styles -->
            <?php foreach ($_SESSION['component_styles'] as $component): ?>
                <link rel="stylesheet" href="<?= asset('css/components/' . $component . '.css') ?>?v=20250816">
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</head>

<body>
    <header class="main-header">
        <nav class="nav">
            <div class="container">
                <a href="<?= urlp('public/index.php') ?>" class="nav-brand">
                    <span class="text-accent">Salameh</span> Cargo
                </a>

                <button class="nav-toggle" aria-label="Toggle navigation">
                    <span class="nav-toggle-icon"></span>
                </button>

                <div class="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?= urlp('public/index.php') ?>" class="nav-link <?php echo ($_SERVER['PHP_SELF'] == '/public/index.php') ? 'active' : ''; ?>">
                                Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= urlp('public/track.php') ?>" class="nav-link <?php echo ($_SERVER['PHP_SELF'] == '/public/track.php') ? 'active' : ''; ?>">
                                Track
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/public/about.php" class="nav-link <?php echo ($_SERVER['PHP_SELF'] == '/public/about.php') ? 'active' : ''; ?>">
                                About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/public/contact.php" class="nav-link <?php echo ($_SERVER['PHP_SELF'] == '/public/contact.php') ? 'active' : ''; ?>">
                                Contact
                            </a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a href="/public/dashboard.php" class="nav-link <?php echo ($_SERVER['PHP_SELF'] == '/public/dashboard.php') ? 'active' : ''; ?>">
                                    Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/public/logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($_SERVER['PHP_SELF'] == '/public/login.php') ? 'active' : ''; ?>" href="/public/login.php">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main>
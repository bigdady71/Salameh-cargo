<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salameh Cargo - Shipment Tracking</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <header class="sticky-header">
        <nav>
            <div class="logo">
                <a href="/public/index.php">Salameh Cargo</a>
            </div>
            <ul class="nav-links">
                <li><a href="/public/index.php">Home</a></li>
                <li><a href="/public/track.php">Track</a></li>
                <li><a href="/public/about.php">About</a></li>
                <li><a href="/public/contact.php">Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/public/dashboard.php">Dashboard</a></li>
                    <li><a href="/public/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/public/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

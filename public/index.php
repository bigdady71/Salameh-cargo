<?php require_once __DIR__ . '/../includes/db.php'; require_once __DIR__ . '/../includes/auth.php'; include __DIR__ . '/../includes/header.php'; ?>
<div class='hero'>
    <h1>Welcome to Salameh Cargo</h1>
    <p>Track your shipments with ease</p>
    <form action='track.php' method='get'><input type='text' name='query'
            placeholder='Enter tracking/container/BL/phone/name' required><button type='submit'>Track Now</button>
    </form>
</div><?php include __DIR__ . '/../includes/footer.php'; ?>
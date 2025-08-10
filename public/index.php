<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="hero">
        <div class="container">
            <h1>Welcome to Salameh Cargo</h1>
            <p>Track your shipments with ease</p>
            <form action="track.php" method="get" class="tracking-form">
                <div class="form-group">
                    <input type="text" name="query" placeholder="Enter tracking/container/BL/phone/name" required>
                </div>
                <button type="submit" class="btn btn-primary">Track Now</button>
            </form>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
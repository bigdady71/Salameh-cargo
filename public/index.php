<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title">World Wide Shipping</h1>
            <p class="hero__subtitle">Shipping from China to Worldwide</p>
            <a href="track.php" class="hero__cta">
                <i class="fas fa-search"></i>
                Track Your Item
            </a>
        </div>
    </section>

    <!-- Services Cards Section -->
    <section class="cards">
        <div class="container">
            <div class="cards__grid">
                <div class="card">
                    <div class="card__icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="card__title">World Wide Shipping</h3>
                    <p class="card__desc">10000+ shipments in 45 countries</p>
                </div>
                <div class="card">
                    <div class="card__icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="card__title">Door To Door Delivery</h3>
                    <p class="card__desc">Shipping from China</p>
                </div>
                <div class="card">
                    <div class="card__icon">
                        <i class="fas fa-ship"></i>
                    </div>
                    <h3 class="card__title">Sea Freight</h3>
                    <p class="card__desc">6000+ containers in 20 ports</p>
                </div>
                <div class="card">
                    <div class="card__icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <h3 class="card__title">Warehousing</h3>
                    <p class="card__desc">Cover 1,000,000 sqm</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why">
        <div class="container">
            <div class="why__header">
                <h2 class="why__title">Why Choose Us?</h2>
            </div>
            <div class="why__grid">
                <div class="why__item">
                    <div class="why__icon">
                        <i class="fas fa-anchor"></i>
                    </div>
                    <h3 class="why__item-title">Sea Freight Forwarder</h3>
                    <p class="why__item-desc">Booking, documentation, and carrier coordination.</p>
                </div>
                <div class="why__item">
                    <div class="why__icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3 class="why__item-title">Sourcing Agent In China</h3>
                    <p class="why__item-desc">End-to-end sourcing and supplier management.</p>
                </div>
                <div class="why__item">
                    <div class="why__icon">
                        <i class="fas fa-earth-asia"></i>
                    </div>
                    <h3 class="why__item-title">China Global Freight</h3>
                    <p class="why__item-desc">Multi-modal logistics across regions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Track Section -->
    <section class="split">
        <div class="container">
            <div class="split__container">
                <div class="split__media">
                    <img src="../assets/images/quick-track.svg" alt="Quick Tracking Service" class="split__image">
                </div>
                <div class="split__body">
                    <h2 class="split__title">Quick Tracking Service</h2>
                    <p class="split__text">Track by tracking number, container, B/L, phone, or name.</p>
                    <a href="track.php" class="split__cta">
                        <i class="fas fa-search"></i>
                        Track Your Item
                    </a>
                </div>
            </div>
        </div>
    </section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

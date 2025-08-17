<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section with 3D Animation -->
<section class="hero position-relative overflow-hidden">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Fast, Reliable Cargo Shipping</h1>
                <p class="hero-subtitle">From China to Worldwide – track your shipment effortlessly.</p>
                <div class="hero-cta">
                    <a href="<?= urlp('public/track.php') ?>" class="btn btn--primary">
                        <i class="fas fa-search"></i>
                        Track Your Item
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <div class="tilt">
                    <img src="<?= asset('images/container-ship-6631117_1280.jpg') ?>" alt="Cargo Ship">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Cards Section -->
<section class="services">
    <div class="container">
        <div class="section-header">
            <h2>Our Services</h2>
            <p>Comprehensive shipping solutions for your business</p>
        </div>

        <div class="services-grid">


            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <img src="../assets/images/word wide shipping 1.jpg" alt="Door to Door Delivery" class="card-img-top" style="height: 250px; object-fit: cover; width: 100%;">
                    <div class="card-body">
                        <h3 class="h5 card-title">World Wide Shipping</h3>
                        <p class="card-text text-muted">Over 10,000 shipments in 45 countries.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <img src="../assets/images/door-to-door-delivery-service.jpeg" alt="Door to Door Delivery" class="card-img-top" style="height: 250px; object-fit: cover; width: 100%;">
                    <div class="card-body">
                        <h3 class="h5 card-title">Door to Door Delivery</h3>
                        <p class="card-text text-muted">From your factory to your doorstep</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <img src="../assets/images/010322-Air-vs-Ocean-Freight.webp" alt="Sea & Air Freight" class="card-img-top" style="height: 250px; object-fit: cover; width: 100%;">
                    <div class="card-body">
                        <h3 class="h5 card-title">Sea & Air Freight</h3>
                        <p class="card-text text-muted">Reliable sea and air cargo solutions</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <img src="../assets/images/010322-Air-vs-Ocean-Freight.webp" alt="Sea & Air Freight" class="card-img-top" style="height: 250px; object-fit: cover; width: 100%;">
                    <div class="card-body">
                        <h3 class="h5 card-title">Sea & Air Freight</h3>
                        <p class="card-text text-muted">Reliable sea and air cargo solutions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <img src="../assets/images/010322-Air-vs-Ocean-Freight.webp" alt="Sea & Air Freight" class="card-img-top" style="height: 250px; object-fit: cover; width: 100%;">
                    <div class="card-body">
                        <h3 class="h5 card-title">Sea & Air Freight</h3>
                        <p class="card-text text-muted">Reliable sea and air cargo solutions</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <img src="../assets/images/download.jpeg" alt="Warehousing" class="card-img-top" style="height: 250px; object-fit: cover; width: 100%;">
                    <div class="card-body">
                        <h3 class="h5 card-title">Warehousing</h3>
                        <p class="card-text text-muted">Safe storage solutions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Why Choose Us?</h2>
            <p class="lead text-muted">We deliver excellence in global logistics</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="card border-0 text-center p-4 shadow-sm hover-lift">
                    <div class="feature-icon bg-primary bg-gradient text-white mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px;">
                        <i class="fas fa-anchor fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-3">Sea Freight Forwarder</h3>
                    <p class="text-muted">Booking, documentation, and carrier coordination for seamless sea freight
                        operations.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 text-center p-4 shadow-sm hover-lift">
                    <div class="feature-icon bg-primary bg-gradient text-white mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px;">
                        <i class="fas fa-user-tie fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-3">Sourcing Agent In China</h3>
                    <p class="text-muted">End-to-end sourcing and supplier management with local expertise.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 text-center p-4 shadow-sm hover-lift">
                    <div class="feature-icon bg-primary bg-gradient text-white mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px;">
                        <i class="fas fa-earth-asia fa-2x"></i>
                    </div>
                    <h3 class="h4 mb-3">China Global Freight</h3>
                    <p class="text-muted">Multi-modal logistics across regions with optimized routing and tracking.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Track Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="position-relative scene" id="track-3d-scene">
                    <div class="layer" data-depth="0.1">
                        <img src="../assets/images/GPS-Tracking-Definition5.jpg" style="width: 90%; margin:2rem; border-radius:10px;" alt="Quick Tracking Service"
                            class="img-fluid transform-rotate">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="p-4 p-lg-5">
                    <h2 class="display-5 fw-bold mb-4">Quick Tracking Service</h2>
                    <p class="lead mb-4">Track your shipment by tracking number, container, B/L, phone, or name - all in
                        real-time.</p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="track.php" class="btn btn-primary btn-lg px-4 me-md-2">
                            <i class="fas fa-search me-2"></i>
                            Track Your Item
                        </a>
                        <a href="contact.php" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="fas fa-headset me-2"></i>
                            Get Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container py-4 text-center">
        <h2 class="display-6 fw-bold mb-4">Ready to Ship with Confidence?</h2>
        <p class="lead mb-4">Join thousands of satisfied customers who trust Salameh Cargo for their shipping needs.</p>
        <a href="contact.php" class="btn btn-light btn-lg px-5 py-3 shadow">
            <i class="fas fa-paper-plane me-2"></i>
            Contact Us Today
        </a>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
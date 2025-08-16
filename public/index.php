<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section with 3D Animation -->
<section class="hero position-relative overflow-hidden py-5 mb-5">
    <div class="hero-bg position-absolute top-0 start-0 w-100 h-100 parallax-bg"></div>
    <div class="container py-5">
        <div class="row min-vh-50 align-items-center">
            <div class="col-lg-6 py-5 text-center text-lg-start">
                <h1 class="display-4 fw-bold mb-4 text-primary animate__animated animate__fadeInUp">Fast, Reliable Cargo
                    Shipping</h1>
                <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">From China to Worldwide –
                    track your shipment effortlessly.</p>
                <div class="animate__animated animate__fadeInUp animate__delay-2s">
                    <a href="track.php" class="btn btn-primary btn-lg px-4 py-3 shadow-sm">
                        <i class="fas fa-search me-2"></i>
                        Track Your Item
                    </a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="position-relative scene" id="hero-3d-scene">
                    <div class="layer" data-depth="0.2">
                        <img src="../assets/images/container-ship-6631117_1280.jpg" alt="Cargo Ship"
                            class="img-fluid rounded-3 shadow-lg transform-perspective">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Cards Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Our Services</h2>
            <p class="lead text-muted">Comprehensive shipping solutions for your business</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <img src="/assets/images/service-1.jpg" alt="World Wide Shipping" class="card-img-top">
                    <div class="card-body">
                        <h3 class="h5 card-title">World Wide Shipping</h3>
                        <p class="card-text text-muted">Over 10,000 shipments in 45 countries.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <img src="/assets/images/service-2.jpg" alt="Door to Door Delivery" class="card-img-top">
                    <div class="card-body">
                        <h3 class="h5 card-title">Door to Door Delivery</h3>
                        <p class="card-text text-muted">From your factory to your doorstep.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <img src="/assets/images/service-3.jpg" alt="Sea & Air Freight" class="card-img-top">
                    <div class="card-body">
                        <h3 class="h5 card-title">Sea & Air Freight</h3>
                        <p class="card-text text-muted">Reliable sea and air cargo solutions.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <img src="/assets/images/service-4.jpg" alt="Warehousing" class="card-img-top">
                    <div class="card-body">
                        <h3 class="h5 card-title">Warehousing</h3>
                        <p class="card-text text-muted">Safe storage solutions in multiple locations.</p>
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
                        <img src="../assets/images/quick-track.svg" alt="Quick Tracking Service"
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
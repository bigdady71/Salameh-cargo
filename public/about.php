<?php 
require_once __DIR__ . '/../includes/db.php'; 
require_once __DIR__ . '/../includes/auth.php'; 
include __DIR__ . '/../includes/header.php'; 
?>

<main>
    <div class="container">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero__content">
                <h1 class="hero__title">About Salameh Cargo</h1>
                <p class="hero__subtitle">Your trusted partner in global shipping and logistics, connecting Lebanon with the world through reliable cargo services.</p>
            </div>
        </section>

        <!-- Company Overview Section -->
        <section class="why">
            <div class="container">
                <div class="why__header">
                    <h2 class="why__title">Our Story</h2>
                </div>
                <div class="why__grid">
                    <div class="why__item">
                        <div class="why__icon">
                            <i class="fas fa-ship"></i>
                        </div>
                        <h3 class="why__item-title">Company Overview</h3>
                        <p class="why__item-desc">
                            Founded with a vision to bridge continents through reliable shipping services, Salameh Cargo has become a trusted name in international logistics. We specialize in connecting Lebanon with global markets, particularly focusing on trade routes between Lebanon and China.
                        </p>
                    </div>

                    <div class="why__item">
                        <div class="why__icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3 class="why__item-title">Our Heritage</h3>
                        <p class="why__item-desc">
                            With years of experience in the shipping industry, we have built strong partnerships and established efficient routes that ensure your cargo reaches its destination safely and on time. Our deep understanding of Middle Eastern and Asian markets sets us apart.
                        </p>
                    </div>

                    <div class="why__item">
                        <div class="why__icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3 class="why__item-title">Global Reach</h3>
                        <p class="why__item-desc">
                            Operating between major ports in China and Lebanon, we facilitate seamless trade for businesses and individuals. Our network spans across key shipping hubs, ensuring comprehensive coverage for all your logistics needs.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="cards">
            <div class="container">
                <h2 style="text-align: center; color: var(--text); font-size: 2.5rem; margin-bottom: 3rem;">Our Services</h2>
                <div class="cards__grid">
                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-container"></i>
                        </div>
                        <h3 class="card__title">Container Shipping</h3>
                        <p class="card__desc">
                            Full container load (FCL) and less than container load (LCL) services with competitive rates and reliable scheduling between China and Lebanon.
                        </p>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-search-location"></i>
                        </div>
                        <h3 class="card__title">Real-Time Tracking</h3>
                        <p class="card__desc">
                            Advanced tracking system that provides real-time updates on your shipment status, from origin to final destination.
                        </p>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h3 class="card__title">Custom Clearance</h3>
                        <p class="card__desc">
                            Expert customs clearance services ensuring smooth and efficient processing of your cargo through all regulatory requirements.
                        </p>
                    </div>

                    <div class="card">
                        <div class="card__icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <h3 class="card__title">Warehousing</h3>
                        <p class="card__desc">
                            Secure warehousing facilities at key locations, providing temporary storage and consolidation services for your cargo.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission & Values Section -->
        <section class="split">
            <div class="container">
                <div class="split__container">
                    <div class="split__body">
                        <h2 class="split__title">Our Mission & Values</h2>
                        <p class="split__text">
                            At Salameh Cargo, our mission is to provide exceptional shipping and logistics services that enable global trade and connect communities. We are committed to reliability, transparency, and customer satisfaction in every shipment we handle.
                        </p>
                        
                        <div style="margin: 2rem 0;">
                            <h4 style="color: var(--accent); margin-bottom: 1rem;">Our Core Values:</h4>
                            <ul style="color: var(--muted); line-height: 2;">
                                <li><strong style="color: var(--text);">Reliability:</strong> On-time delivery you can count on</li>
                                <li><strong style="color: var(--text);">Transparency:</strong> Clear communication and real-time tracking</li>
                                <li><strong style="color: var(--text);">Excellence:</strong> Continuous improvement in all our services</li>
                                <li><strong style="color: var(--text);">Integrity:</strong> Honest and ethical business practices</li>
                            </ul>
                        </div>

                        <a href="/public/contact.php" class="split__cta">
                            <i class="fas fa-phone"></i>
                            Contact Our Team
                        </a>
                    </div>
                    <div class="split__media">
                        <img src="/assets/images/services-1.svg" alt="Global Shipping Network" class="split__image">
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="hero" style="min-height: 50vh; padding: 4rem 2rem;">
            <div class="hero__content">
                <h2 class="hero__title" style="font-size: 2.5rem;">Ready to Ship with Us?</h2>
                <p class="hero__subtitle">
                    Experience reliable shipping services with real-time tracking and dedicated customer support.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="/public/track.php" class="hero__cta">
                        <i class="fas fa-search"></i>
                        Track Your Shipment
                    </a>
                    <a href="/public/contact.php" class="btn btn-secondary">
                        <i class="fas fa-envelope"></i>
                        Get a Quote
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

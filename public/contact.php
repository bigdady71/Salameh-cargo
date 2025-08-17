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
                <h1 class="hero__title">Contact Us</h1>
                <p class="hero__subtitle">Get in touch with our team for all your shipping and logistics needs. We're here to help you every step of the way.</p>
            </div>
        </section>

        <!-- Contact Information Section -->
        <section class="why">
            <div class="container">
                <div class="why__header">
                    <h2 class="why__title">Our Offices</h2>
                </div>
                <div class="why__grid">
                    <!-- Lebanon Office -->
                    <div class="why__item">
                        <div class="why__icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3 class="why__item-title">Lebanon Office</h3>
                        <div class="why__item-desc">
                            <p><strong>Address:</strong><br>
                            Salameh Building, Beirut Port Road<br>
                            Beirut, Lebanon</p>
                            
                            <p><strong>Phone:</strong><br>
                            <a href="tel:+96171123456" style="color: var(--accent);">
                                <i class="fas fa-phone"></i> +961 71 123 456
                            </a></p>
                            
                            <p><strong>Email:</strong><br>
                            <a href="mailto:lebanon@salamehcargo.com" style="color: var(--accent);">
                                <i class="fas fa-envelope"></i> lebanon@salamehcargo.com
                            </a></p>
                            
                            <p><strong>Working Hours:</strong><br>
                            Monday - Friday: 8:00 AM - 6:00 PM<br>
                            Saturday: 8:00 AM - 2:00 PM</p>
                        </div>
                    </div>

                    <!-- China Office -->
                    <div class="why__item">
                        <div class="why__icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3 class="why__item-title">China Office</h3>
                        <div class="why__item-desc">
                            <p><strong>Address:</strong><br>
                            Suite 1205, Logistics Plaza<br>
                            Guangzhou Port, Guangdong Province<br>
                            China 510000</p>
                            
                            <p><strong>Phone:</strong><br>
                            <a href="tel:+8613812345678" style="color: var(--accent);">
                                <i class="fas fa-phone"></i> +86 138 1234 5678
                            </a></p>
                            
                            <p><strong>Email:</strong><br>
                            <a href="mailto:china@salamehcargo.com" style="color: var(--accent);">
                                <i class="fas fa-envelope"></i> china@salamehcargo.com
                            </a></p>
                            
                            <p><strong>Working Hours:</strong><br>
                            Monday - Friday: 9:00 AM - 6:00 PM<br>
                            Saturday: 9:00 AM - 1:00 PM</p>
                        </div>
                    </div>

                    <!-- 24/7 Support -->
                    <div class="why__item">
                        <div class="why__icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="why__item-title">24/7 Customer Support</h3>
                        <div class="why__item-desc">
                            <p><strong>Emergency Hotline:</strong><br>
                            <a href="tel:+96171999888" style="color: var(--accent);">
                                <i class="fas fa-phone-alt"></i> +961 71 999 888
                            </a></p>
                            
                            <p><strong>General Inquiries:</strong><br>
                            <a href="mailto:info@salamehcargo.com" style="color: var(--accent);">
                                <i class="fas fa-envelope"></i> info@salamehcargo.com
                            </a></p>
                            
                            <p><strong>WhatsApp Support:</strong><br>
                            Available 24/7 for instant assistance</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- WhatsApp Contact Section -->
        <section class="split">
            <div class="container">
                <div class="split__container">
                    <div class="split__body">
                        <h2 class="split__title">Instant Support via WhatsApp</h2>
                        <p class="split__text">
                            Get immediate assistance from our customer support team through WhatsApp. Whether you need to track a shipment, get a quote, or have questions about our services, we're just a message away.
                        </p>
                        
                        <div style="margin: 2rem 0;">
                            <p style="color: var(--muted); margin-bottom: 2rem;">
                                <i class="fas fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i>
                                Instant responses during business hours<br>
                                <i class="fas fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i>
                                Track your shipments in real-time<br>
                                <i class="fas fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i>
                                Get shipping quotes quickly<br>
                                <i class="fas fa-check" style="color: var(--accent); margin-right: 0.5rem;"></i>
                                Multilingual support (Arabic, English, Chinese)
                            </p>
                        </div>

                        <a href="https://wa.me/96171123456?text=Hello%20Salameh%20Cargo!%20I%20need%20assistance%20with%20my%20shipment." 
                           class="hero__cta" 
                           target="_blank" 
                           style="background: #25D366; color: white; font-size: 1.2rem; padding: 1.2rem 2.5rem;">
                            <i class="fab fa-whatsapp" style="font-size: 1.5rem; margin-right: 0.75rem;"></i>
                            Chat with us on WhatsApp
                        </a>
                    </div>
                    <div class="split__media">
                        <img src="/assets/images/smart-warehousing.webp" alt="Customer Support" class="split__image">
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Form Section -->
        <section class="cards" style="background: #0D2D52;">
            <div class="container">
                <h2 style="text-align: center; color: white; font-size: 2.5rem; margin-bottom: 3rem;">Send Us a Message</h2>

                <div style="max-width: 800px; margin: 0 auto;">
                    <div class="card" style="padding: 3rem;">
                        <form method="post" action="#" class="contact-form">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="name">Full Name *</label>
                                    <input type="text" id="name" name="name" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone">
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject">Subject *</label>
                                    <select id="subject" name="subject" required>
                                        <option value="">Select a subject</option>
                                        <option value="general">General Inquiry</option>
                                        <option value="quote">Request Quote</option>
                                        <option value="tracking">Shipment Tracking</option>
                                        <option value="complaint">Complaint</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" rows="6" required 
                                          placeholder="Please provide details about your inquiry..."></textarea>
                            </div>
                            
                            <div style="text-align: center; margin-top: 2rem;">
                                <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">
                                    <i class="fas fa-paper-plane"></i>
                                    Send Message
                                </button>
                            </div>
                            
                            <p style="text-align: center; color: var(--muted); font-size: 0.9rem; margin-top: 1rem;">
                                We typically respond within 24 hours during business days.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Links Section -->
        <section class="hero" style="min-height: 40vh; padding: 4rem 2rem;">
            <div class="hero__content">
                <h2 class="hero__title" style="font-size: 2rem;">Need Something Else?</h2>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
                    <a href="/public/track.php" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Track Shipment
                    </a>
                    <a href="/public/about.php" class="btn btn-secondary">
                        <i class="fas fa-info-circle"></i>
                        About Us
                    </a>
                    <a href="/public/login.php" class="btn btn-secondary">
                        <i class="fas fa-user"></i>
                        Customer Portal
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
@media (max-width: 768px) {
    .contact-form > div[style*="grid-template-columns"] {
        display: block !important;
    }
    
    .contact-form .form-group {
        margin-bottom: 1.5rem;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

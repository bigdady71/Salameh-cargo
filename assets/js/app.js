// Salameh Cargo JavaScript

document.addEventListener("DOMContentLoaded", function () {
    // Initialize all features
    initTheme();
    initializeInteractiveElements();
    initHeroParallax();
    initNavigation();

    // Add tilt effect to service cards
    document.querySelectorAll(".service-card").forEach((card) => {
        card.classList.add("tilt");
    });
});

// Theme management
function initTheme() {
    const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");
    const currentTheme = localStorage.getItem("theme");
    const themeToggle = document.querySelector("#theme-toggle");

    // Set initial theme
    if (
        currentTheme === "dark" ||
        (!currentTheme && prefersDarkScheme.matches)
    ) {
        document.documentElement.setAttribute("data-theme", "dark");
    }

    // Theme toggle handler
    if (themeToggle) {
        themeToggle.addEventListener("click", () => {
            const theme = document.documentElement.getAttribute("data-theme");
            const newTheme = theme === "dark" ? "light" : "dark";

            document.documentElement.setAttribute("data-theme", newTheme);
            localStorage.setItem("theme", newTheme);

            // Update toggle appearance
            themeToggle.setAttribute("aria-label", `Switch to ${theme} mode`);
        });
    }

    // Listen for system theme changes
    prefersDarkScheme.addEventListener("change", (e) => {
        if (!localStorage.getItem("theme")) {
            document.documentElement.setAttribute(
                "data-theme",
                e.matches ? "dark" : "light"
            );
        }
    });
}

// Interactive elements
function initializeInteractiveElements() {
    // 3D Tilt Effect
    document.querySelectorAll(".tilt").forEach((el) => {
        el.style.transformStyle = "preserve-3d";
        let timeout;

        el.addEventListener("mousemove", (e) => {
            if (timeout) clearTimeout(timeout);

            const rect = el.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;

            requestAnimationFrame(() => {
                el.style.transform = `
                    perspective(1000px) 
                    rotateX(${(-y * 6).toFixed(2)}deg) 
                    rotateY(${(x * 6).toFixed(2)}deg) 
                    scale3d(1.02, 1.02, 1.02)
                `;
            });
        });

        el.addEventListener("mouseleave", () => {
            timeout = setTimeout(() => {
                el.style.transform = "none";
            }, 100);
        });
    });
}

// Parallax Effect
function initHeroParallax() {
    const hero = document.querySelector(".hero");
    const heroContent = document.querySelector(".hero-content");

    if (hero && heroContent) {
        window.addEventListener("scroll", () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * 0.35;

            // Move background slower than content
            hero.style.backgroundPositionY = `${rate}px`;

            // Fade out content slightly as user scrolls
            const opacity = 1 - Math.min(rate / 500, 0.3);
            heroContent.style.opacity = opacity;
            heroContent.style.transform = `translateY(${rate * 0.5}px)`;
        });
    }
}
function initializeInteractiveElements() {
    // Card hover effects
    const cards = document.querySelectorAll(".card-hover");
    cards.forEach((card) => {
        card.addEventListener("mousemove", handleTilt);
        card.addEventListener("mouseleave", resetTilt);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", (e) => {
            e.preventDefault();
            const target = document.querySelector(anchor.getAttribute("href"));
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        });
    });
}

// Hero parallax effect
function initHeroParallax() {
    const hero = document.querySelector(".hero");
    if (hero) {
        window.addEventListener("scroll", () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * 0.5;
            hero.style.backgroundPosition = `50% ${rate}px`;
        });
    }
}

// Card tilt effect handlers
function handleTilt(e) {
    const card = e.currentTarget;
    const cardRect = card.getBoundingClientRect();
    const centerX = cardRect.left + cardRect.width / 2;
    const centerY = cardRect.top + cardRect.height / 2;
    const mouseX = e.clientX - centerX;
    const mouseY = e.clientY - centerY;

    const rotateX = (mouseY / (cardRect.height / 2)) * -5;
    const rotateY = (mouseX / (cardRect.width / 2)) * 5;

    card.style.transform = `
        perspective(1000px) 
        rotateX(${rotateX}deg) 
        rotateY(${rotateY}deg)
        translateZ(10px)
    `;
}

function resetTilt(e) {
    const card = e.currentTarget;
    card.style.transform =
        "perspective(1000px) rotateX(0) rotateY(0) translateZ(0)";
}

// Navigation menu toggle
function initNavigation() {
    const navToggle = document.querySelector(".nav-toggle");
    const navMenu = document.querySelector(".nav-menu");

    if (navToggle && navMenu) {
        navToggle.addEventListener("click", () => {
            navToggle.classList.toggle("active");
            navMenu.classList.toggle("active");

            // Update ARIA attributes
            const isExpanded = navToggle.classList.contains("active");
            navToggle.setAttribute("aria-expanded", isExpanded);

            // Prevent body scroll when menu is open
            document.body.style.overflow = isExpanded ? "hidden" : "";
        });

        // Close menu on link click
        const navLinks = document.querySelectorAll(".nav-link");
        navLinks.forEach((link) => {
            link.addEventListener("click", () => {
                navToggle.classList.remove("active");
                navMenu.classList.remove("active");
                document.body.style.overflow = "";
            });
        });

        // Close menu on outside click
        document.addEventListener("click", (e) => {
            if (
                navMenu.classList.contains("active") &&
                !navMenu.contains(e.target) &&
                !navToggle.contains(e.target)
            ) {
                navToggle.classList.remove("active");
                navMenu.classList.remove("active");
                document.body.style.overflow = "";
            }
        });

        // Handle escape key
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && navMenu.classList.contains("active")) {
                navToggle.classList.remove("active");
                navMenu.classList.remove("active");
                document.body.style.overflow = "";
            }
        });
    }
}

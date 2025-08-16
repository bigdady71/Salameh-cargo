// Salameh Cargo JavaScript

document.addEventListener('DOMContentLoaded', function() {
    console.log('Salameh Cargo loaded');
    
    // Initialize Animate.css animations
    const animateElements = document.querySelectorAll('.animate__animated');
    animateElements.forEach(element => {
        element.classList.add('animate__animated');
    });

    // Initialize Parallax effect for hero background
    const parallaxBg = document.querySelector('.parallax-bg');
    if (parallaxBg) {
        window.addEventListener('scroll', function() {
            const scrollPosition = window.pageYOffset;
            parallaxBg.style.transform = 'translateY(' + scrollPosition * 0.4 + 'px)';
        });
    }

    // 3D Transform effects for images
    const perspectiveElements = document.querySelectorAll('.transform-perspective');
    perspectiveElements.forEach(element => {
        element.addEventListener('mousemove', function(e) {
            const { left, top, width, height } = element.getBoundingClientRect();
            const x = (e.clientX - left) / width - 0.5;
            const y = (e.clientY - top) / height - 0.5;
            
            element.style.transform = `perspective(1000px) rotateY(${x * 10}deg) rotateX(${y * -10}deg) scale3d(1.05, 1.05, 1.05)`;
        });
        
        element.addEventListener('mouseleave', function() {
            element.style.transform = 'perspective(1000px) rotateY(0) rotateX(0) scale3d(1, 1, 1)';
        });
    });

    // Rotate animation for tracking icon
    const rotateElements = document.querySelectorAll('.transform-rotate');
    rotateElements.forEach(element => {
        element.addEventListener('mouseover', function() {
            element.style.transform = 'rotate(5deg) scale(1.05)';
        });
        
        element.addEventListener('mouseleave', function() {
            element.style.transform = 'rotate(0) scale(1)';
        });
    });

    // Card hover effects
    const hoverCards = document.querySelectorAll('.hover-card');
    hoverCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('shadow');
            this.style.transform = 'translateY(-10px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('shadow');
            this.style.transform = 'translateY(0)';
        });
    });

    // Feature card lift effect
    const liftElements = document.querySelectorAll('.hover-lift');
    liftElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Theme Toggle Functionality (for future dark mode)
    // This sets up the infrastructure for theme switching
    const getStoredTheme = () => localStorage.getItem('theme') || 'light';
    const setStoredTheme = theme => localStorage.setItem('theme', theme);

    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme();
        if (storedTheme) {
            return storedTheme;
        }
        
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const setTheme = theme => {
        document.documentElement.setAttribute('data-theme', theme);
    };

    // Set initial theme
    setTheme(getPreferredTheme());

    // Add theme toggle button to the DOM (commented out for future implementation)
    /*
    const createThemeToggle = () => {
        const toggleContainer = document.createElement('div');
        toggleContainer.className = 'theme-toggle-container';
        toggleContainer.innerHTML = `
            <button class="theme-toggle btn btn-sm" aria-label="Toggle theme">
                <i class="fas fa-sun"></i>
                <i class="fas fa-moon"></i>
            </button>
        `;
        
        document.body.appendChild(toggleContainer);
        
        const themeToggle = document.querySelector('.theme-toggle');
        themeToggle.addEventListener('click', () => {
            const currentTheme = getStoredTheme();
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            setStoredTheme(newTheme);
            setTheme(newTheme);
        });
    };
    
    // Uncomment to enable theme toggle
    // createThemeToggle();
    */
});
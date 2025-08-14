// Modern Personal Website JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript loaded successfully!');
    
    // Theme Toggle
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = themeToggle.querySelector('i');
    const body = document.body;
    
    console.log('Theme toggle element found:', themeToggle);
    console.log('Theme icon element found:', themeIcon);
    
    // Check for saved theme preference or default to light mode
    // Temporarily clear localStorage to test from scratch
    // localStorage.clear();
    const currentTheme = localStorage.getItem('theme') || 'light';
    console.log('Current theme from localStorage:', currentTheme);
    
    body.setAttribute('data-theme', currentTheme);
    updateThemeUI(currentTheme);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = body.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        console.log('Theme toggle clicked:', currentTheme, '->', newTheme);
        
        body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeUI(newTheme);
    });
    
    function updateThemeUI(theme) {
        console.log('Updating theme UI to:', theme);
        if (theme === 'dark') {
            themeIcon.textContent = '💡';
            themeIcon.className = '';
            console.log('Set icon to lightbulb (dark mode)');
        } else {
            themeIcon.textContent = '💡';
            themeIcon.className = '';
            console.log('Set icon to lightbulb (light mode)');
        }
        
        // Force a re-render by briefly hiding and showing the icon
        themeIcon.style.opacity = '0';
        setTimeout(() => {
            themeIcon.style.opacity = '1';
        }, 10);
    }
    
    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 80; // Account for sticky header
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    

    

    
    // Skill item hover effects
    const skillItems = document.querySelectorAll('.skill-item');
    
    skillItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Mobile menu functionality
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const headerNav = document.getElementById('header-nav');
    
    if (mobileMenuToggle && headerNav) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
            headerNav.classList.toggle('mobile-open');
            console.log('Mobile menu toggled:', headerNav.classList.contains('mobile-open'));
        });
        
        // Close mobile menu when clicking on a link
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenuToggle.classList.remove('active');
                headerNav.classList.remove('mobile-open');
                console.log('Mobile menu closed via link click');
            });
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenuToggle.contains(event.target) && !headerNav.contains(event.target)) {
                mobileMenuToggle.classList.remove('active');
                headerNav.classList.remove('mobile-open');
                console.log('Mobile menu closed via outside click');
            }
        });
    } else {
        console.error('Mobile menu elements not found:', { mobileMenuToggle, headerNav });
    }
    
    // Header scroll effect
    const header = document.querySelector('.header');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 50) {
            header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        } else {
            header.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
        }
    });
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animatedElements = document.querySelectorAll('.skill-category, .contact-item');
    animatedElements.forEach(element => {
        observer.observe(element);
    });
    
    // Loading animation
    window.addEventListener('load', function() {
        document.body.classList.add('loaded');
    });
    
    // Performance optimization: Throttle scroll events
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        }
    }
    
    // Apply throttling to scroll events
    const throttledScrollHandler = throttle(function() {
        if (window.pageYOffset > 50) {
            header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        } else {
            header.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
        }
    }, 16); // ~60fps
    
    window.addEventListener('scroll', throttledScrollHandler);
    
    // Add micro-interactions
    addMicroInteractions();
    
    function addMicroInteractions() {
        // Button hover effects
        const buttons = document.querySelectorAll('.hero-btn, .theme-toggle, .project-link');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-1px)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Link hover effects
        const links = document.querySelectorAll('.footer-link, .text-link');
        links.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-1px)';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Social link hover effects
        const socialLinks = document.querySelectorAll('.social-link');
        socialLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // Accessibility improvements
    improveAccessibility();
    
    function improveAccessibility() {
        // Add ARIA labels
        const themeToggle = document.getElementById('theme-toggle');
        themeToggle.setAttribute('aria-label', 'Toggle dark mode');
        
        // Add keyboard navigation for project cards
        projectCards.forEach(card => {
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'button');
            
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const liveButton = this.querySelector('.project-link');
                    if (liveButton) {
                        liveButton.click();
                    }
                }
            });
        });
        
        // Add keyboard navigation for skill items
        skillItems.forEach(item => {
            item.setAttribute('tabindex', '0');
            item.setAttribute('role', 'button');
        });
    }
    
    // Console message
    console.log('🎨 Modern Personal Website loaded successfully!');
    console.log('💡 Tip: Use the theme toggle in the header to switch between light and dark modes');
});

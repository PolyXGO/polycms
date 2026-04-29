/**
 * PolyCMS Corporate Theme JavaScript
 *
 * Professional business theme functionality
 */

(function() {
    'use strict';

    // Mobile Menu Toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuToggle && mobileMenu) {
        // Update ARIA attributes
        mobileMenuToggle.setAttribute('aria-expanded', 'false');
        mobileMenu.setAttribute('aria-hidden', 'true');

        mobileMenuToggle.addEventListener('click', function() {
            const isActive = mobileMenu.classList.toggle('active');

            // Update ARIA attributes
            mobileMenuToggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');
            mobileMenu.setAttribute('aria-hidden', isActive ? 'false' : 'true');

            // Update icon
            const icon = mobileMenuToggle.querySelector('svg');
            if (icon) {
                if (isActive) {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
                    icon.setAttribute('aria-label', 'Close menu');
                } else {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                    icon.setAttribute('aria-label', 'Open menu');
                }
            }
        });

        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                mobileMenu.setAttribute('aria-hidden', 'true');
                mobileMenuToggle.focus();
            }
        });
    }

    // Header scroll effect
    const header = document.getElementById('main-header');
    if (header) {
        let lastScroll = 0;
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;

            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        });
    }

    // Smooth scroll for anchor links
    // Respect prefers-reduced-motion
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    const headerOffset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: prefersReducedMotion ? 'auto' : 'smooth'
                    });

                    // Close mobile menu if open
                    if (mobileMenu && mobileMenu.classList.contains('active')) {
                        mobileMenu.classList.remove('active');
                        // Update icon
                        const icon = mobileMenuToggle.querySelector('svg');
                        if (icon) {
                            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                        }
                    }

                    // Focus target for accessibility
                    target.setAttribute('tabindex', '-1');
                    target.focus();
                }
            }
        });
    });

    // Card hover effects - removed to prevent layout shift
    // Cards now use CSS-only hover effects (box-shadow changes only)

    // Lazy loading images
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
        });
    } else {
        // Fallback for browsers that don't support lazy loading
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }

    // Intersection Observer for fade-in animations
    // Respect prefers-reduced-motion (using variable declared above)
    if (!prefersReducedMotion) {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.card, .home-section__widgets > *').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    }

    // Product gallery interactions
    const galleryViewport = document.querySelector('.product-gallery__viewport');
    if (galleryViewport) {
        const slides = Array.from(galleryViewport.querySelectorAll('.product-gallery__slide'));
        const thumbButtons = Array.from(document.querySelectorAll('.product-gallery__thumb'));

        const setActiveSlide = (index) => {
            slides.forEach((slide, slideIndex) => {
                slide.classList.toggle('product-gallery__slide--active', slideIndex === index);
            });

            thumbButtons.forEach((thumb, thumbIndex) => {
                thumb.classList.toggle('product-gallery__thumb--active', thumbIndex === index);
            });
        };

        thumbButtons.forEach((thumb, index) => {
            thumb.addEventListener('click', () => setActiveSlide(index));
        });
    }
    // ============================================
    // Listing View Toggle (Grid / List)
    // ============================================
    document.querySelectorAll('.listing-view-toggle').forEach(toggleGroup => {
        const targetId = toggleGroup.dataset.listingTarget;
        const container = targetId ? document.getElementById(targetId) : null;
        if (!container) return;

        const storageKey = 'polycms_view_' + targetId;
        const buttons = toggleGroup.querySelectorAll('.view-toggle-btn');

        // Restore saved preference
        const saved = localStorage.getItem(storageKey);
        if (saved && (saved === 'grid' || saved === 'list')) {
            container.classList.remove('is-grid', 'is-list');
            container.classList.add('is-' + saved);
            buttons.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.view === saved);
            });
        }

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const view = btn.dataset.view;
                container.classList.remove('is-grid', 'is-list');
                container.classList.add('is-' + view);

                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                localStorage.setItem(storageKey, view);
            });
        });
    });

    // ============================================
    // Post Scroll Tools
    // ============================================
    const scrollToTopBtn = document.getElementById('scroll-to-top');
    if (scrollToTopBtn) {
        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: prefersReducedMotion ? 'auto' : 'smooth'
            });
        });
    }

    const scrollToBottomBtn = document.getElementById('scroll-to-bottom');
    if (scrollToBottomBtn) {
        scrollToBottomBtn.addEventListener('click', () => {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: prefersReducedMotion ? 'auto' : 'smooth'
            });
        });
    }

    console.log('PolyCMS Corporate Theme loaded successfully!');
})();


/**
 * FlexiMyTa Theme JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (mobileMenu && mobileMenuToggle) {
            if (!mobileMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                mobileMenu.classList.remove('active');
            }
        }
    });
    
    // Header scroll effect
    const header = document.getElementById('main-header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            if (e.defaultPrevented) return;
            if (
                this.dataset.noSmoothScroll === 'true' ||
                this.closest('.product-detail-tabs') ||
                this.matches('[data-tab-target], .view-all-packages-link')
            ) {
                return;
            }

            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const headerHeight = header ? header.offsetHeight : 70;
                    const targetPosition = target.offsetTop - headerHeight;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                    // Close mobile menu if open
                    if (mobileMenu) {
                        mobileMenu.classList.remove('active');
                    }
                }
            }
        });
    });
    
    // FAQ Toggle
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const container = question.closest('.faq-item');
            const answer = question.nextElementSibling;
            const icon = question.querySelector('i');
            const isActive = answer.classList.contains('active');
            
            // Toggle current FAQ
            if (!isActive) {
                answer.classList.add('active');
                answer.style.height = 'fit-content';
                answer.style.opacity = '1';
                if (icon) icon.style.transform = 'rotate(180deg)';
            } else {
                answer.classList.remove('active');
                answer.style.height = '0px';
                answer.style.opacity = '0';
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        });
    });
    
    // Demo Video Play
    const playDemo = document.getElementById('playDemo');
    if (playDemo) {
        playDemo.addEventListener('click', function() {
            alert("In a real implementation, this would open a modal with a video player. For now, you can direct users to your actual demo URL.");
            window.open('https://youtu.be/e4hWG23B0No', '_blank');
        });
    }

    // Product Gallery
    const productGalleries = document.querySelectorAll('.product-gallery');
    productGalleries.forEach((gallery) => {
        const slides = Array.from(gallery.querySelectorAll('.product-gallery-slide'));
        const thumbs = Array.from(gallery.querySelectorAll('.product-gallery-thumb'));
        const viewport = gallery.querySelector('.product-gallery-viewport');
        const thumbsContainer = gallery.querySelector('.product-gallery-thumbs');
        const prevBtn = gallery.querySelector('[data-action="prev"]');
        const nextBtn = gallery.querySelector('[data-action="next"]');
        const thumbsPrevBtn = gallery.querySelector('[data-action="thumbs-prev"]');
        const thumbsNextBtn = gallery.querySelector('[data-action="thumbs-next"]');

        if (!slides.length) {
            return;
        }

        let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains('active')));
        if (activeIndex === -1) activeIndex = 0;
        let suppressThumbClick = false;

        const setActive = (index) => {
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;

            activeIndex = index;

            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === activeIndex);
            });

            thumbs.forEach((thumb, i) => {
                const isActive = i === activeIndex;
                thumb.classList.toggle('active', isActive);
                if (isActive) {
                    thumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                }
            });
        };

        thumbs.forEach((thumb, index) => {
            thumb.addEventListener('click', (event) => {
                if (suppressThumbClick) {
                    event.preventDefault();
                    event.stopPropagation();
                    suppressThumbClick = false;
                    return;
                }
                setActive(index);
            });
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', () => setActive(activeIndex - 1));
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => setActive(activeIndex + 1));
        }

        if (thumbsPrevBtn && thumbsContainer) {
            thumbsPrevBtn.addEventListener('click', () => {
                thumbsContainer.scrollBy({ left: -240, behavior: 'smooth' });
            });
        }
        if (thumbsNextBtn && thumbsContainer) {
            thumbsNextBtn.addEventListener('click', () => {
                thumbsContainer.scrollBy({ left: 240, behavior: 'smooth' });
            });
        }

        // Swipe / drag on large viewport (mobile, iPad, mouse drag)
        if (viewport) {
            let startX = 0;
            let startY = 0;
            let isPointerDown = false;
            let hasMoved = false;
            const swipeThreshold = 38;

            viewport.addEventListener('pointerdown', (event) => {
                isPointerDown = true;
                hasMoved = false;
                startX = event.clientX;
                startY = event.clientY;
            });

            viewport.addEventListener('pointermove', (event) => {
                if (!isPointerDown) return;
                const deltaX = event.clientX - startX;
                const deltaY = event.clientY - startY;
                if (Math.abs(deltaX) > 6 || Math.abs(deltaY) > 6) {
                    hasMoved = true;
                }
            });

            const finishSwipe = (event) => {
                if (!isPointerDown) return;
                const deltaX = event.clientX - startX;
                const deltaY = event.clientY - startY;
                isPointerDown = false;

                // Horizontal swipe only
                if (!hasMoved || Math.abs(deltaX) < swipeThreshold || Math.abs(deltaX) < Math.abs(deltaY)) {
                    return;
                }

                if (deltaX < 0) {
                    setActive(activeIndex + 1);
                } else {
                    setActive(activeIndex - 1);
                }
            };

            viewport.addEventListener('pointerup', finishSwipe);
            viewport.addEventListener('pointercancel', () => {
                isPointerDown = false;
            });
            viewport.addEventListener('pointerleave', (event) => {
                if (isPointerDown) finishSwipe(event);
            });
        }

        // Drag-to-scroll on thumbs strip (mouse + touch/pointer)
        if (thumbsContainer) {
            let isDraggingThumbs = false;
            let startX = 0;
            let startScrollLeft = 0;
            let movedDistance = 0;

            const onPointerMove = (event) => {
                if (!isDraggingThumbs) return;
                const dx = event.clientX - startX;
                movedDistance = Math.max(movedDistance, Math.abs(dx));
                if (movedDistance > 10) {
                    suppressThumbClick = true;
                    event.preventDefault();
                }
                thumbsContainer.scrollLeft = startScrollLeft - dx;
            };

            const endDrag = () => {
                isDraggingThumbs = false;
                thumbsContainer.classList.remove('dragging');
                window.setTimeout(() => {
                    suppressThumbClick = false;
                }, 120);
            };

            thumbsContainer.addEventListener('pointerdown', (event) => {
                if (event.button !== 0 && event.pointerType !== 'touch') return;
                isDraggingThumbs = true;
                startX = event.clientX;
                startScrollLeft = thumbsContainer.scrollLeft;
                movedDistance = 0;
                thumbsContainer.classList.add('dragging');
            });

            thumbsContainer.addEventListener('pointermove', onPointerMove);
            thumbsContainer.addEventListener('pointerup', endDrag);
            thumbsContainer.addEventListener('pointercancel', endDrag);
            thumbsContainer.addEventListener('pointerleave', endDrag);
        }

        setActive(activeIndex);
    });

    // Initialize active FAQs height
    document.querySelectorAll('.faq-answer.active').forEach(answer => {
        // Use fit-content so default-open FAQs remain visible
        // even when their parent tab/panel is hidden during initial render.
        answer.style.height = 'fit-content';
        answer.style.opacity = '1';
        const icon = answer.previousElementSibling.querySelector('i');
        if (icon) icon.style.transform = 'rotate(180deg)';
    });
});

/**
 * BannerSlider Module - Core Script
 * PolyCMS Module Asset
 */
(function() {
    const bannerContainer = document.getElementById('banner-slider-container');
    const slider = document.getElementById('banner-slider');
    
    // Dynamic adjustment for Fixed/Sticky Headers to prevent overlap
    if (bannerContainer) {
        const adjustHeaders = function() {
            const bannerHeight = bannerContainer.offsetHeight || 0;
            const topbarActive = document.body.classList.contains('polycms-topbar-active');
            const baseOffset = topbarActive ? 32 : 0;
            
            // Calculate effective banner height based on scroll position
            const scrollY = window.scrollY || window.pageYOffset;
            const effectiveBannerHeight = Math.max(0, bannerHeight - scrollY);
            const totalOffset = baseOffset + effectiveBannerHeight;
            
            const headers = document.querySelectorAll('header, .header, .main-header, .site-header');
            
            headers.forEach(header => {
                const computedStyle = window.getComputedStyle(header);
                if (computedStyle.position === 'fixed') {
                    // Update offset dynamically without lag by disabling top transition
                    header.style.setProperty('top', `${totalOffset}px`, 'important');
                    header.style.transitionProperty = 'background-color, box-shadow, color, opacity';
                } else if (computedStyle.position === 'sticky') {
                    header.style.setProperty('top', `${baseOffset}px`, 'important');
                }
            });
        };
        
        setTimeout(adjustHeaders, 50);
        
        window.addEventListener('scroll', function() {
            window.requestAnimationFrame(adjustHeaders);
        }, { passive: true });
        
        if (window.ResizeObserver) {
            new ResizeObserver(adjustHeaders).observe(bannerContainer);
        }
        
        if (window.MutationObserver) {
            new MutationObserver((mutations) => {
                for (const mutation of mutations) {
                    if (mutation.attributeName === 'class') {
                        adjustHeaders();
                    }
                }
            }).observe(document.body, { attributes: true });
        }
    }

    if (!slider || slider.children.length <= 1) return;

    // Get settings from data attributes
    const autoSlide = bannerContainer?.dataset.autoSlide === 'true';
    const autoSlideInterval = parseInt(bannerContainer?.dataset.autoSlideInterval || '5000');
    const transitionEffect = bannerContainer?.dataset.transitionEffect || 'slide';
    const showNavigation = bannerContainer?.dataset.showNavigation === 'true';
    const showIndicators = bannerContainer?.dataset.showIndicators === 'true';

    // Hide navigation/indicators if disabled
    if (!showNavigation) {
        const prevBtn = document.querySelector('.banner-slider-prev');
        const nextBtn = document.querySelector('.banner-slider-next');
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
    }

    if (!showIndicators) {
        const dotsContainer = document.querySelector('.banner-slider-dots');
        if (dotsContainer) dotsContainer.style.display = 'none';
    }

    // Apply transition effect
    slider.setAttribute('data-effect', transitionEffect);
    let slides = Array.from(slider.children);
    const isFadeZoomEffect = ['fade', 'fade-in', 'fade-out', 'zoom', 'zoom-in', 'zoom-out'].includes(transitionEffect);
    const isSlideEffect = ['slide', 'slide-right', 'slide-top', 'slide-bottom'].includes(transitionEffect);

    let currentSlide = 0;
    let totalSlides = slides.length;
    let realTotalSlides = slides.length;

    // For infinite loop slide effects, clone first and last slides
    if (isSlideEffect && slides.length > 1) {
        // Store original slides count before cloning
        const originalCount = slides.length;

        // For slide (Left to Right from user's view), reverse the order of slides in DOM to create right-to-left effect
        if (transitionEffect === 'slide') {
            // Reverse the slides array
            const reversedSlides = Array.from(slides).reverse();
            // Clear slider
            slider.innerHTML = '';
            // Append reversed slides
            reversedSlides.forEach(slide => slider.appendChild(slide));
            // Update slides array
            slides = Array.from(slider.children);
        }

        // For slide-top (Top to Bottom from user's view) and slide-bottom (Bottom to Top from user's view), reverse the order of slides in DOM
        if (transitionEffect === 'slide-top' || transitionEffect === 'slide-bottom') {
            // Reverse the slides array
            const reversedSlides = Array.from(slides).reverse();
            // Clear slider
            slider.innerHTML = '';
            // Append reversed slides
            reversedSlides.forEach(slide => slider.appendChild(slide));
            // Update slides array
            slides = Array.from(slider.children);
        }

        // Clone last slide and prepend to start
        const lastSlide = slides[slides.length - 1];
        const lastClone = lastSlide.cloneNode(true);
        lastClone.classList.add('banner-slide-clone');
        slider.insertBefore(lastClone, slides[0]);

        // Clone first slide and append to end
        const firstSlide = slides[0];
        const firstClone = firstSlide.cloneNode(true);
        firstClone.classList.add('banner-slide-clone');
        slider.appendChild(firstClone);

        // Update slides array
        slides = Array.from(slider.children);
        totalSlides = slides.length;
        realTotalSlides = originalCount;

        // Start at the real first slide (index 1, since we prepended a clone)
        currentSlide = 1;

        // Set initial transform to show first real slide
        let initialTransform = '';
        switch(transitionEffect) {
            case 'slide':
            case 'slide-right':
                initialTransform = `translateX(-${currentSlide * 100}%)`;
                break;
            case 'slide-top':
            case 'slide-bottom':
                initialTransform = `translateY(-${currentSlide * 100}%)`;
                break;
            default:
                initialTransform = `translateX(-${currentSlide * 100}%)`;
        }
        slider.style.transform = initialTransform;

        // Set height based on the tallest slide (for all slide effects)
        // Wait for slides to render, then calculate max height
        setTimeout(() => {
            let maxHeight = 0;
            // Get all real slides (skip clones if they exist)
            const realSlides = totalSlides > realTotalSlides
                ? slides.slice(1, totalSlides - 1) // Skip first clone and last clone
                : slides;

            // Calculate height for each real slide and find the maximum
            realSlides.forEach(slide => {
                // Ensure slide is visible to measure
                const originalDisplay = slide.style.display;
                slide.style.display = 'block';

                // Force reflow to ensure accurate measurement
                void slide.offsetHeight;

                const slideHeight = Math.max(
                    slide.offsetHeight || 0,
                    slide.scrollHeight || 0,
                    slide.clientHeight || 0
                );

                if (slideHeight > maxHeight) {
                    maxHeight = slideHeight;
                }

                // Restore original display
                slide.style.display = originalDisplay || '';
            });

            if (maxHeight > 0) {
                // For vertical slides, set height on slider, container, and wrapper
                if (transitionEffect === 'slide-top' || transitionEffect === 'slide-bottom') {
                    slider.style.height = maxHeight + 'px';
                    if (bannerContainer) {
                        bannerContainer.style.height = maxHeight + 'px';
                    }
                    const wrapper = slider.parentElement;
                    if (wrapper) {
                        wrapper.style.height = maxHeight + 'px';
                    }
                } else {
                    // For horizontal slides, ensure container has enough height
                    if (bannerContainer) {
                        bannerContainer.style.minHeight = maxHeight + 'px';
                    }
                    const wrapper = slider.parentElement;
                    if (wrapper) {
                        wrapper.style.minHeight = maxHeight + 'px';
                    }
                }
            }
        }, 150);

        // Restore transition after a brief moment
        setTimeout(() => {
            slider.style.transition = '';
        }, 50);
    }

    if (isFadeZoomEffect) {
        slides.forEach((slide, index) => {
            slide.setAttribute('data-effect', transitionEffect);
            if (index === currentSlide) {
                slide.style.display = 'block';
                // Small delay to ensure display is set before adding active class
                setTimeout(() => {
                    slide.classList.add('active');
                }, 10);
            } else {
                slide.classList.remove('active');
                slide.style.display = 'none';
            }
        });
    } else if (isSlideEffect) {
        // For slide effects, ensure all slides are visible
        slides.forEach(slide => {
            slide.style.display = 'block';
        });

    }
    const prevBtn = document.querySelector('.banner-slider-prev');
    const nextBtn = document.querySelector('.banner-slider-next');
    const dots = document.querySelectorAll('.banner-dot');

    let isUpdating = false; // Prevent multiple simultaneous updates

    function updateSlider(instant) {
        instant = instant || false;
        const effect = transitionEffect;

        // Set transition based on instant flag
        if (instant) {
            slider.style.transition = 'none';
        } else {
            // Get transition duration from CSS or use default
            const transitionDuration = '0.5s';
            slider.style.transition = `transform ${transitionDuration} ease-in-out`;
        }

        if (['fade', 'fade-in', 'fade-out', 'zoom', 'zoom-in', 'zoom-out'].includes(effect)) {
            // Fade/Zoom effects - hide all, show current with transition
            slides.forEach((slide, index) => {
                slide.setAttribute('data-effect', effect);
                if (index === currentSlide) {
                    // Show current slide
                    slide.style.display = 'block';
                    // Force reflow before adding active class
                    void slide.offsetWidth;
                    slide.classList.add('active');
                } else {
                    // Hide other slides
                    slide.classList.remove('active');
                    // Delay hiding to allow fade-out transition
                    if (slide.style.display !== 'none') {
                        setTimeout(() => {
                            if (!slide.classList.contains('active')) {
                                slide.style.display = 'none';
                            }
                        }, 500);
                    } else {
                        slide.style.display = 'none';
                    }
                }
            });
        } else if (isSlideEffect) {
            // Slide effects with infinite loop
            let transform = '';

            switch(effect) {
                case 'slide':
                case 'slide-right':
                    transform = `translateX(-${currentSlide * 100}%)`;
                    break;
                case 'slide-top':
                case 'slide-bottom':
                    transform = `translateY(-${currentSlide * 100}%)`;
                    break;
                default:
                    transform = `translateX(-${currentSlide * 100}%)`;
            }

            // Apply transform immediately - force browser to apply it
            slider.style.transform = transform;
            // Force reflow to ensure transform is applied
            void slider.offsetWidth;

            // Ensure all slides are visible for slide effects
            slides.forEach(slide => {
                slide.style.display = 'block';
            });

            // Set height based on the tallest slide (for all slide effects)
            let maxHeight = 0;
            // Get all real slides (skip clones if they exist)
            const realSlides = totalSlides > realTotalSlides
                ? slides.slice(1, totalSlides - 1) // Skip first clone and last clone
                : slides;

            // Calculate height for each real slide and find the maximum
            realSlides.forEach(slide => {
                // Ensure slide is visible to measure
                const originalDisplay = slide.style.display;
                slide.style.display = 'block';

                // Force reflow to ensure accurate measurement
                void slide.offsetHeight;

                const slideHeight = Math.max(
                    slide.offsetHeight || 0,
                    slide.scrollHeight || 0,
                    slide.clientHeight || 0
                );

                if (slideHeight > maxHeight) {
                    maxHeight = slideHeight;
                }

                // Restore original display
                slide.style.display = originalDisplay || '';
            });

            if (maxHeight > 0) {
                // For vertical slides, set height on slider, container, and wrapper
                if (effect === 'slide-top' || effect === 'slide-bottom') {
                    slider.style.height = maxHeight + 'px';
                    if (bannerContainer) {
                        bannerContainer.style.height = maxHeight + 'px';
                    }
                    const wrapper = slider.parentElement;
                    if (wrapper) {
                        wrapper.style.height = maxHeight + 'px';
                    }
                } else {
                    // For horizontal slides, ensure container has enough height
                    if (bannerContainer) {
                        bannerContainer.style.minHeight = maxHeight + 'px';
                    }
                    const wrapper = slider.parentElement;
                    if (wrapper) {
                        wrapper.style.minHeight = maxHeight + 'px';
                    }
                }
            }

            // Handle infinite loop - jump to real slide when reaching clones
            if (isSlideEffect && totalSlides > realTotalSlides && !instant) {
                if (currentSlide === 0) {
                    // At cloned last slide, jump to real last slide (without animation)
                    setTimeout(() => {
                        slider.style.transition = 'none';
                        currentSlide = realTotalSlides;
                        let jumpTransform = '';
                        switch(effect) {
                            case 'slide':
                            case 'slide-right':
                                jumpTransform = `translateX(-${currentSlide * 100}%)`;
                                break;
                            case 'slide-top':
                            case 'slide-bottom':
                                jumpTransform = `translateY(-${currentSlide * 100}%)`;
                                break;
                        }
                        slider.style.transform = jumpTransform;
                        setTimeout(() => {
                            slider.style.transition = '';
                        }, 50);
                    }, 500);
                } else if (currentSlide === totalSlides - 1) {
                    // At cloned first slide, jump to real first slide (without animation)
                    setTimeout(() => {
                        slider.style.transition = 'none';
                        currentSlide = 1;
                        let jumpTransform = '';
                        switch(effect) {
                            case 'slide':
                            case 'slide-right':
                                jumpTransform = `translateX(-${currentSlide * 100}%)`;
                                break;
                            case 'slide-top':
                            case 'slide-bottom':
                                jumpTransform = `translateY(-${currentSlide * 100}%)`;
                                break;
                        }
                        slider.style.transform = jumpTransform;
                        setTimeout(() => {
                            slider.style.transition = '';
                        }, 50);
                    }, 500);
                }
            }
        }

        // Update dots (use real slide index)
        let realSlideIndex = currentSlide;
        if (isSlideEffect && totalSlides > realTotalSlides) {
            if (currentSlide === 0) {
                realSlideIndex = realTotalSlides - 1;
            } else if (currentSlide === totalSlides - 1) {
                realSlideIndex = 0;
            } else {
                realSlideIndex = currentSlide - 1;
            }
        }

        // For slide (Left to Right from user's view), slide-top (Top to Bottom from user's view), and slide-bottom (Bottom to Top from user's view), reverse the dot index (since slides are reversed in DOM)
        if ((transitionEffect === 'slide' || transitionEffect === 'slide-top' || transitionEffect === 'slide-bottom') && realSlideIndex >= 0) {
            realSlideIndex = realTotalSlides - 1 - realSlideIndex;
        }

        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === realSlideIndex);
        });
    }

    function nextSlide() {
        if (isUpdating) return;
        isUpdating = true;

        // For slide (Left to Right from user's view), slide-top (Top to Bottom from user's view), and slide-bottom (Bottom to Top from user's view), reverse the navigation direction
        if (transitionEffect === 'slide' || transitionEffect === 'slide-top' || transitionEffect === 'slide-bottom') {
            // For right-to-left / bottom-to-top / top-to-bottom (from user's view), "next" means going backwards (decreasing index)
            if (isSlideEffect && totalSlides > realTotalSlides) {
                // Infinite loop: move to previous (which is "next" for right-to-left / bottom-to-top / top-to-bottom)
                currentSlide--;
                // If we've reached the cloned last slide, let updateSlider handle the jump
                if (currentSlide < 0) {
                    currentSlide = 0;
                }
            } else {
                // Normal loop
                currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            }
        } else {
            // Normal navigation (for slide-right, which is Left to Right from user's view)
            if (isSlideEffect && totalSlides > realTotalSlides) {
                // Infinite loop: move to next, including clones
                currentSlide++;
                // If we've reached the cloned first slide, let updateSlider handle the jump
                if (currentSlide >= totalSlides) {
                    currentSlide = totalSlides - 1;
                }
            } else {
                // Normal loop
                currentSlide = (currentSlide + 1) % totalSlides;
            }
        }

        // Update slider immediately - this will apply the transform
        updateSlider();

        // Reset flag after transition completes (500ms + buffer)
        setTimeout(() => {
            isUpdating = false;
        }, 600);
    }

    function prevSlide() {
        if (isUpdating) return;
        isUpdating = true;

        // For slide (Left to Right from user's view), slide-top (Top to Bottom from user's view), and slide-bottom (Bottom to Top from user's view), reverse the navigation direction
        if (transitionEffect === 'slide' || transitionEffect === 'slide-top' || transitionEffect === 'slide-bottom') {
            // For right-to-left / bottom-to-top / top-to-bottom (from user's view), "prev" means going forwards (increasing index)
            if (isSlideEffect && totalSlides > realTotalSlides) {
                // Infinite loop: move to next (which is "prev" for right-to-left / bottom-to-top / top-to-bottom)
                currentSlide++;
                // If we've reached the cloned first slide, let updateSlider handle the jump
                if (currentSlide >= totalSlides) {
                    currentSlide = totalSlides - 1;
                }
            } else {
                // Normal loop
                currentSlide = (currentSlide + 1) % totalSlides;
            }
        } else {
            // Normal navigation (for slide-right, which is Left to Right from user's view)
            if (isSlideEffect && totalSlides > realTotalSlides) {
                // Infinite loop: move to previous, including clones
                currentSlide--;
                // If we've reached the cloned last slide, let updateSlider handle the jump
                if (currentSlide < 0) {
                    currentSlide = 0;
                }
            } else {
                // Normal loop
                currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            }
        }

        // Update slider immediately - this will apply the transform
        updateSlider();

        // Reset flag after transition completes (500ms + buffer)
        setTimeout(() => {
            isUpdating = false;
        }, 600);
    }

    function goToSlide(index) {
        // For slide (Left to Right from user's view), slide-top (Top to Bottom from user's view), and slide-bottom (Bottom to Top from user's view), reverse the index (since slides are reversed in DOM)
        let targetIndex = index;
        if (transitionEffect === 'slide' || transitionEffect === 'slide-top' || transitionEffect === 'slide-bottom') {
            targetIndex = realTotalSlides - 1 - index;
        }

        if (isSlideEffect && totalSlides > realTotalSlides) {
            // For infinite loop, adjust index to account for cloned first slide
            currentSlide = targetIndex + 1;
        } else {
            currentSlide = targetIndex;
        }
        updateSlider();
    }

    // Auto-play
    let autoplayInterval = null;
    let isHoveringNavigation = false;
    let isHoveringIndicator = false;

    function pauseAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
            autoplayInterval = null;
        }
    }

    function resumeAutoplay() {
        if (autoSlide && !autoplayInterval && !isHoveringNavigation && !isHoveringIndicator) {
            autoplayInterval = setInterval(nextSlide, autoSlideInterval);
        }
    }

    if (autoSlide) {
        autoplayInterval = setInterval(nextSlide, autoSlideInterval);

        // Pause on hover over banner container
        if (bannerContainer) {
            bannerContainer.addEventListener('mouseenter', () => {
                pauseAutoplay();
            });
            bannerContainer.addEventListener('mouseleave', () => {
                resumeAutoplay();
            });
        }

        // Pause on hover over navigation buttons
        if (prevBtn) {
            prevBtn.addEventListener('mouseenter', () => {
                isHoveringNavigation = true;
                pauseAutoplay();
            });
            prevBtn.addEventListener('mouseleave', () => {
                isHoveringNavigation = false;
                resumeAutoplay();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('mouseenter', () => {
                isHoveringNavigation = true;
                pauseAutoplay();
            });
            nextBtn.addEventListener('mouseleave', () => {
                isHoveringNavigation = false;
                resumeAutoplay();
            });
        }

        // Pause on hover over indicators
        dots.forEach((dot) => {
            dot.addEventListener('mouseenter', () => {
                isHoveringIndicator = true;
                pauseAutoplay();
            });
            dot.addEventListener('mouseleave', () => {
                isHoveringIndicator = false;
                resumeAutoplay();
            });
        });
    }

    // Click handlers - pause autoplay when clicking
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            pauseAutoplay();
            prevSlide();
            // Resume after a delay to allow user to continue interacting
            setTimeout(() => {
                if (!isHoveringNavigation && !isHoveringIndicator) {
                    resumeAutoplay();
                }
            }, autoSlideInterval);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            pauseAutoplay();
            nextSlide();
            // Resume after a delay to allow user to continue interacting
            setTimeout(() => {
                if (!isHoveringNavigation && !isHoveringIndicator) {
                    resumeAutoplay();
                }
            }, autoSlideInterval);
        });
    }

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            pauseAutoplay();
            goToSlide(index);
            // Resume after a delay to allow user to continue interacting
            setTimeout(() => {
                if (!isHoveringNavigation && !isHoveringIndicator) {
                    resumeAutoplay();
                }
            }, autoSlideInterval);
        });
    });

    // Respect prefers-reduced-motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
            autoplayInterval = null;
        }
        slider.style.transition = 'none';
    }

    // Touch/Swipe/Drag support
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;
    let touchStartTime = 0;
    let isDragging = false;
    let dragOffset = 0;
    let isTransitioning = false;
    const minSwipeDistance = 30; // Minimum distance in pixels to trigger swipe (reduced for lighter swipes)
    const minSwipeVelocity = 0.3; // Minimum velocity (pixels per ms) for fast swipes

    // Resume autoplay after interaction (only if not hovering navigation/indicators)
    function resumeAutoplayAfterInteraction() {
        if (autoSlide && !autoplayInterval && !isDragging && !isHoveringNavigation && !isHoveringIndicator) {
            autoplayInterval = setInterval(nextSlide, autoSlideInterval);
        }
    }

    // Touch start
    function handleTouchStart(e) {
        if (isTransitioning) return;

        const touch = e.touches ? e.touches[0] : e;
        touchStartX = touch.clientX;
        touchStartY = touch.clientY;
        touchStartTime = Date.now();
        isDragging = true;
        dragOffset = 0;

        pauseAutoplay();

        // Prevent default to avoid scrolling
        if (e.cancelable) {
            e.preventDefault();
        }

        slider.style.transition = 'none';
    }

    // Touch move
    function handleTouchMove(e) {
        if (!isDragging || isTransitioning) return;

        const touch = e.touches ? e.touches[0] : e;
        touchEndX = touch.clientX;
        touchEndY = touch.clientY;

        const deltaX = touchEndX - touchStartX;
        const deltaY = touchEndY - touchStartY;

        // Determine if horizontal or vertical swipe
        const isHorizontal = Math.abs(deltaX) > Math.abs(deltaY);

        if (!isHorizontal && (transitionEffect.includes('slide-top') || transitionEffect.includes('slide-bottom'))) {
            // Vertical swipe for vertical slide effects
            dragOffset = deltaY;
        } else if (isHorizontal && !transitionEffect.includes('slide-top') && !transitionEffect.includes('slide-bottom')) {
            // Horizontal swipe for horizontal slide effects
            dragOffset = deltaX;
        } else {
            return; // Don't handle if direction doesn't match effect
        }

        // Only apply drag for slide effects, not fade/zoom
        // Start showing drag feedback even with small movements
        if (!isFadeZoomEffect && Math.abs(dragOffset) > 3) {
            const baseTransform = -currentSlide * 100;
            let transform = '';

            switch(transitionEffect) {
                case 'slide':
                    transform = `translateX(calc(${baseTransform}% + ${dragOffset}px))`;
                    break;
                case 'slide-right':
                    // For slide-right, use normal calculation (slides are already reversed in DOM)
                    transform = `translateX(calc(-${currentSlide * 100}% + ${dragOffset}px))`;
                    break;
                case 'slide-top':
                case 'slide-bottom':
                    transform = `translateY(calc(-${currentSlide * 100}% + ${dragOffset}px))`;
                    break;
                default:
                    transform = `translateX(calc(${baseTransform}% + ${dragOffset}px))`;
            }

            slider.style.transform = transform;
        }

        // Prevent default scrolling when dragging horizontally (or vertically for vertical slides)
        if (e.cancelable) {
            const isH = Math.abs(deltaX) > Math.abs(deltaY);
            const shouldPrevent = (isH && !transitionEffect.includes('slide-top') && !transitionEffect.includes('slide-bottom')) ||
                                 (!isH && (transitionEffect.includes('slide-top') || transitionEffect.includes('slide-bottom')));

            if (shouldPrevent && Math.abs(dragOffset) > 5) {
                e.preventDefault();
            }
        }
    }

    // Touch end
    function handleTouchEnd(e) {
        if (!isDragging) return;

        isDragging = false;
        isTransitioning = true;

        const touchEndTime = Date.now();
        const deltaX = touchEndX - touchStartX;
        const deltaY = touchEndY - touchStartY;
        const deltaTime = touchEndTime - touchStartTime;
        const delta = Math.abs(deltaX) > Math.abs(deltaY) ? deltaX : deltaY;
        const deltaAbs = Math.abs(delta);

        // Calculate velocity (pixels per millisecond)
        const velocity = deltaTime > 0 ? deltaAbs / deltaTime : 0;

        // Restore transition
        slider.style.transition = '';
        slider.style.transform = '';

        // Determine swipe direction and change slide
        // Trigger if: distance >= minSwipeDistance OR velocity >= minSwipeVelocity (for fast light swipes)
        const shouldSwipe = deltaAbs >= minSwipeDistance || velocity >= minSwipeVelocity;

        if (shouldSwipe) {
            const isHorizontal = Math.abs(deltaX) > Math.abs(deltaY);

            if (isHorizontal && !transitionEffect.includes('slide-top') && !transitionEffect.includes('slide-bottom')) {
                // Horizontal swipe
                if (transitionEffect === 'slide') {
                    // For slide (Left to Right from user's view), reverse swipe direction
                    if (deltaX > 0) {
                        // Swipe right - go to next (which is prev for right-to-left)
                        nextSlide();
                    } else {
                        // Swipe left - go to previous (which is next for right-to-left)
                        prevSlide();
                    }
                } else {
                    // Normal left-to-right (for slide-right)
                    if (deltaX > 0) {
                        // Swipe right - go to previous
                        prevSlide();
                    } else {
                        // Swipe left - go to next
                        nextSlide();
                    }
                }
            } else if (!isHorizontal && (transitionEffect.includes('slide-top') || transitionEffect.includes('slide-bottom'))) {
                // Vertical swipe
                if (transitionEffect === 'slide-top' || transitionEffect === 'slide-bottom') {
                    // For slide-top (Top to Bottom from user's view) and slide-bottom (Bottom to Top from user's view), reverse swipe direction
                    if (deltaY > 0) {
                        // Swipe down - go to next (which is prev for bottom-to-top / top-to-bottom)
                        nextSlide();
                    } else {
                        // Swipe up - go to previous (which is next for bottom-to-top / top-to-bottom)
                        prevSlide();
                    }
                }
            }
        } else {
            // Not enough distance or velocity, just update to current slide
            updateSlider();
        }

        // Reset touch values
        touchStartX = 0;
        touchStartY = 0;
        touchEndX = 0;
        touchEndY = 0;
        touchStartTime = 0;
        dragOffset = 0;

        // Resume autoplay after a short delay
        setTimeout(() => {
            isTransitioning = false;
            resumeAutoplayAfterInteraction();
        }, 300);
    }

    // Add touch event listeners
    if (bannerContainer) {
        // Touch events
        bannerContainer.addEventListener('touchstart', handleTouchStart, { passive: false });
        bannerContainer.addEventListener('touchmove', handleTouchMove, { passive: false });
        bannerContainer.addEventListener('touchend', handleTouchEnd, { passive: true });
        bannerContainer.addEventListener('touchcancel', handleTouchEnd, { passive: true });

        // Mouse events for desktop drag support
        bannerContainer.addEventListener('mousedown', handleTouchStart);
        bannerContainer.addEventListener('mousemove', handleTouchMove);
        bannerContainer.addEventListener('mouseup', handleTouchEnd);
        bannerContainer.addEventListener('mouseleave', handleTouchEnd);

        // Prevent image drag
        bannerContainer.addEventListener('dragstart', (e) => {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
            }
        });

        // Apply hover styles to buttons with data-hover attributes
        function applyButtonHoverStyles() {
            const buttons = document.querySelectorAll('.banner-cta-button[data-hover-bg], .banner-cta-button[data-hover-color], .banner-cta-button[data-hover-background]');
            buttons.forEach(button => {
                // Skip if already has event listeners (prevent duplicate)
                if (button.dataset.hoverApplied) return;

                const hoverBg = button.getAttribute('data-hover-bg');
                const hoverColor = button.getAttribute('data-hover-color');
                const hoverBackground = button.getAttribute('data-hover-background');
                const originalStyle = button.getAttribute('style') || '';

                if (hoverBg || hoverColor || hoverBackground) {
                    button.dataset.hoverApplied = 'true';

                    button.addEventListener('mouseenter', function() {
                        if (hoverBackground) {
                            this.style.background = hoverBackground;
                        } else if (hoverBg) {
                            this.style.backgroundColor = hoverBg;
                        }
                        if (hoverColor) {
                            this.style.color = hoverColor;
                        }
                    });

                    button.addEventListener('mouseleave', function() {
                        // Reset to original style
                        this.setAttribute('style', originalStyle);
                    });
                }
            });
        }

        // Apply hover styles after a short delay to ensure DOM is ready
        setTimeout(applyButtonHoverStyles, 100);

        // Check if user is admin and show edit icons
        (function() {
            function checkAdminAndShowEditIcons() {
                // Check if user has auth token in localStorage
                const authToken = localStorage.getItem('auth_token');
                if (!authToken) {
                    return; // No token, user not logged in
                }

                // Check if user is admin by calling API
                fetch('/api/v1/auth/me', {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                    throw new Error('Not authenticated');
                })
                .then(data => {
                    // API returns: { data: { id, name, email, roles: [...], permissions: [...] } }
                    if (data && data.data && data.data.roles) {
                        const roles = data.data.roles;
                        // Check if user has admin role
                        const isAdmin = Array.isArray(roles) && roles.includes('admin');

                        if (isAdmin) {
                            // Show all edit icons
                            const editIcons = document.querySelectorAll('.banner-edit-icon');
                            editIcons.forEach(icon => {
                                icon.style.display = 'flex';
                            });
                        }
                    }
                })
                .catch(error => {
                    // User not authenticated or error, hide icons (already hidden by default)
                    console.debug('Banner edit icons: User not authenticated or error checking admin status');
                });
            }

            // Check after DOM is ready and after a short delay to ensure banner slider is initialized
            setTimeout(() => {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', checkAdminAndShowEditIcons);
                } else {
                    checkAdminAndShowEditIcons();
                }
            }, 200);
        })();
    }

    // Countdown timers
    const countdownElements = document.querySelectorAll('.banner-countdown[data-countdown-date]');
    const countdownIntervals = [];

    countdownElements.forEach((countdownEl) => {
        const countdownDate = countdownEl.dataset.countdownDate;
        if (!countdownDate) return;

        const target = new Date(countdownDate).getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = target - now;

            if (distance < 0) {
                const expiredHtml = '<div class="countdown-expired">Countdown expired</div>';
                if (countdownEl.innerHTML !== expiredHtml) {
                    countdownEl.innerHTML = expiredHtml;
                }
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const daysEl = countdownEl.querySelector('[data-days]');
            const hoursEl = countdownEl.querySelector('[data-hours]');
            const minutesEl = countdownEl.querySelector('[data-minutes]');
            const secondsEl = countdownEl.querySelector('[data-seconds]');

            if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
            if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
            if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
        }

        updateCountdown();
        const intervalId = setInterval(updateCountdown, 1000);
        countdownIntervals.push(intervalId);
    });
})();

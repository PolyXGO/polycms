/**
 * Common countdown timer function for Banner Slider
 * Can be used in both admin preview and public frontend
 *
 * @param {HTMLElement|string} element - The countdown container element or selector
 * @param {string|Date} targetDate - The target date for countdown (ISO string or Date object)
 * @param {Object} options - Optional configuration
 * @param {Function} options.onExpired - Callback when countdown expires
 * @param {Function} options.onUpdate - Callback on each update (receives {days, hours, minutes, seconds})
 * @returns {Function} Cleanup function to stop the countdown
 */
export function initCountdown(element, targetDate, options = {}) {
    const container = typeof element === 'string' ? document.querySelector(element) : element;
    if (!container) {
        console.warn('Countdown container not found');
        return () => {};
    }

    const target = typeof targetDate === 'string' ? new Date(targetDate).getTime() : targetDate.getTime();
    const { onExpired, onUpdate } = options;

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = target - now;

        if (distance < 0) {
            // Countdown expired
            const expiredHtml = '<div class="countdown-expired">Countdown expired</div>';
            if (container.innerHTML !== expiredHtml) {
                container.innerHTML = expiredHtml;
            }
            if (onExpired) {
                onExpired();
            }
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Update DOM elements
        const daysEl = container.querySelector('[data-days]');
        const hoursEl = container.querySelector('[data-hours]');
        const minutesEl = container.querySelector('[data-minutes]');
        const secondsEl = container.querySelector('[data-seconds]');

        if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
        if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
        if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
        if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');

        // Call update callback
        if (onUpdate) {
            onUpdate({ days, hours, minutes, seconds });
        }
    }

    // Initial update
    updateCountdown();

    // Update every second
    const intervalId = setInterval(updateCountdown, 1000);

    // Return cleanup function
    return () => {
        clearInterval(intervalId);
    };
}

/**
 * Initialize all countdown timers on the page
 * Looks for elements with class 'banner-countdown' and data-countdown-date attribute
 */
export function initAllCountdowns() {
    const countdownElements = document.querySelectorAll('.banner-countdown[data-countdown-date]');
    const cleanupFunctions = [];

    countdownElements.forEach((countdownEl) => {
        const countdownDate = countdownEl.dataset.countdownDate;
        if (countdownDate) {
            const cleanup = initCountdown(countdownEl, countdownDate);
            cleanupFunctions.push(cleanup);
        }
    });

    // Return cleanup function for all countdowns
    return () => {
        cleanupFunctions.forEach(cleanup => cleanup());
    };
}

// Auto-initialize on DOM ready if in browser environment
if (typeof window !== 'undefined' && typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllCountdowns);
    } else {
        initAllCountdowns();
    }
}

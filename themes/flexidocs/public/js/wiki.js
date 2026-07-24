// FlexiDocs JavaScript

document.addEventListener('alpine:init', () => {
    Alpine.data('wikiToc', () => ({
        sidebarOpen: window.innerWidth >= 1024,
        headings: [],
        activeId: null,
        initToc() {
            setTimeout(() => {
                const content = document.querySelector('.wiki-content');
                if (!content) return;
                
                const headingElements = content.querySelectorAll('h2, h3, h4');
                this.headings = Array.from(headingElements).map(el => {
                    if (!el.id) {
                        el.id = el.innerText.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                    }
                    return {
                        id: el.id,
                        text: el.innerText,
                        level: parseInt(el.tagName.charAt(1))
                    };
                });

                const scrollContainer = document.querySelector('.wiki-scroll-container');

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.activeId = entry.target.id;
                        }
                    });
                }, { 
                    root: scrollContainer,
                    rootMargin: '-10% 0px -80% 0px'
                });

                headingElements.forEach(el => observer.observe(el));
            }, 100);
        },
        get activeHeadingText() {
            if (this.headings.length === 0) return '';
            const active = this.headings.find(h => h.id === this.activeId);
            return active ? active.text : this.headings[0].text;
        },
        scrollToHeading(id, index) {
            if (index === 0) {
                const scrollContainer = document.querySelector('.wiki-scroll-container');
                if (scrollContainer) {
                    scrollContainer.scrollTo({ top: 0, behavior: 'smooth' });
                    history.pushState(null, null, '#' + id);
                    return;
                }
            }
            
            const el = document.getElementById(id);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
                history.pushState(null, null, '#' + id);
            }
        }
    }));
});

// ── Sidebar client-side menu filter ──
(function() {
    'use strict';

    const filterInput = document.querySelector('[data-sidebar-filter-input]');
    const filterContainer = document.querySelector('[data-sidebar-filter-container]');
    if (!filterInput || !filterContainer) return;

    function filterSidebar() {
        const query = filterInput.value.trim().toLowerCase();

        // Find all sidebar link groups
        const groups = filterContainer.querySelectorAll('.wiki-group');

        groups.forEach(function(group) {
            const links = group.querySelectorAll('li');
            let hasVisible = false;

            links.forEach(function(li) {
                const text = li.textContent.toLowerCase();
                const match = !query || text.indexOf(query) !== -1;
                li.classList.toggle('sidebar-filter-hidden', !match);
                if (match) hasVisible = true;
            });

            // Hide group heading if no visible items
            const heading = group.querySelector('h3');
            if (heading) {
                heading.classList.toggle('sidebar-filter-hidden', !hasVisible && links.length > 0);
            }

            // Hide "No articles yet" text when filtering
            const noArticles = group.querySelector('.text-xs.text-slate-400.italic');
            if (noArticles && query) {
                noArticles.classList.add('sidebar-filter-hidden');
            } else if (noArticles) {
                noArticles.classList.remove('sidebar-filter-hidden');
            }
        });

        // Handle orphan list items (direct children of container)
        const orphanLists = filterContainer.querySelectorAll(':scope > ul, :scope nav > ul');
        orphanLists.forEach(function(ul) {
            const items = ul.querySelectorAll('li');
            let hasVisible = false;
            items.forEach(function(li) {
                const text = li.textContent.toLowerCase();
                const match = !query || text.indexOf(query) !== -1;
                li.classList.toggle('sidebar-filter-hidden', !match);
                if (match) hasVisible = true;
            });
        });
    }

    filterInput.addEventListener('input', filterSidebar);
})();
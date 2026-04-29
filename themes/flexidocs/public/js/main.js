// FlexiDocs JavaScript

document.addEventListener('alpine:init', () => {
    Alpine.data('wikiToc', () => ({
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

                const observer = new IntersectionObserver((entries) => {
                    let visibleHeadings = [];
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            visibleHeadings.push(entry.target);
                        }
                    });
                    
                    if (visibleHeadings.length > 0) {
                        this.activeId = visibleHeadings[0].id;
                    }
                }, { rootMargin: '0px 0px -80% 0px' });

                headingElements.forEach(el => observer.observe(el));
            }, 100);
        },
        scrollToHeading(id, index) {
            if (index === 0) {
                const scrollContainer = document.querySelector('main');
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

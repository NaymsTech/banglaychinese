

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Staggered fade-in used by the footer columns.
 *
 * Usage: <div x-data="revealItem(120)" :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
 * Elements start visible in the markup (safe without JS); once Alpine boots they
 * are faded out and revealed when they scroll into view.
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('revealItem', (delay = 0) => ({
        visible: false,
        delay,
        init() {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const supported = 'IntersectionObserver' in window;

            if (reduceMotion || !supported) {
                this.visible = true;
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        this.visible = true;
                        observer.disconnect();
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

            observer.observe(this.$el);
        },
    }));
});

Alpine.start();

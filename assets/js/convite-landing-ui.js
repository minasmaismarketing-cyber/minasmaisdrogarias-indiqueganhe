/**
 * Landing /convite — animações visuais (Sprint 3.5).
 * Não altera Smart Script nem geração de OneLink.
 */
(function () {
    'use strict';

    function initFadeIn() {
        var landing = document.querySelector('.convite-landing__card');

        if (!landing) {
            return;
        }

        window.requestAnimationFrame(function () {
            landing.classList.add('convite-landing__card--visible');
        });

        var animated = landing.querySelectorAll('[data-animate]');

        if (!('IntersectionObserver' in window)) {
            animated.forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }

        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            { threshold: 0.15, rootMargin: '0px 0px -8px 0px' }
        );

        animated.forEach(function (el) {
            observer.observe(el);
        });
    }

    function initCtaPulse() {
        var button = document.getElementById('convite-download-btn');

        if (!button || button.getAttribute('aria-disabled') === 'true') {
            return;
        }

        window.setInterval(function () {
            button.classList.add('convite-landing__cta--pulse');

            window.setTimeout(function () {
                button.classList.remove('convite-landing__cta--pulse');
            }, 800);
        }, 6000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initFadeIn();
            initCtaPulse();
        });
    } else {
        initFadeIn();
        initCtaPulse();
    }
})();

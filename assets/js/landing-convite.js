/**
 * Landing Convite — Sprint 3.5.1
 * Apenas animações de entrada e pulse do CTA.
 * Smart Script permanece em convite-landing.js (layout).
 */
(function () {
    'use strict';

    function revealCard() {
        var card = document.querySelector('[data-lc-card]');
        if (!card) {
            return;
        }

        requestAnimationFrame(function () {
            card.classList.add('is-visible');
        });
    }

    function revealStaggered() {
        var items = document.querySelectorAll('[data-lc-animate]');
        if (!items.length) {
            return;
        }

        items.forEach(function (item, index) {
            setTimeout(function () {
                item.classList.add('is-visible');
            }, 120 + index * 80);
        });
    }

    function startCtaPulse() {
        var cta = document.getElementById('convite-download-btn');
        if (!cta) {
            return;
        }

        var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!prefersReduced) {
            cta.classList.add('is-pulsing');
        }
    }

    function init() {
        revealCard();
        revealStaggered();
        startCtaPulse();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

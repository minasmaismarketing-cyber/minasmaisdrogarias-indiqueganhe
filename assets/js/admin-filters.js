/**
 * Admin filter panels — Sprint 1.6.3
 * Collapsible GET filter forms (desktop + mobile, same behavior).
 */
(function () {
    document.querySelectorAll('[data-admin-filter-panel]').forEach(function (panel) {
        var toggle = panel.querySelector('[data-admin-filter-toggle]');
        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', function () {
            var isOpen = panel.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });
})();

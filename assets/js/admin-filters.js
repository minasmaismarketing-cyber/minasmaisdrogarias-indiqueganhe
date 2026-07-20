/**
 * Admin filter panels — Sprint 1.6.3
 * Collapsible GET filter forms (desktop + mobile, same behavior).
 *
 * Admin expandable tables — mobile accordion (max-width: 768px).
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

    var mobileMq = window.matchMedia('(max-width: 991.98px)');

    function isMobileAccordion() {
        return mobileMq.matches;
    }

    function closeRow(trigger, panel) {
        trigger.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        if (panel) {
            panel.hidden = true;
        }
    }

    function openRow(trigger, panel, table) {
        table.querySelectorAll('[data-admin-accordion-trigger].is-open').forEach(function (openTrigger) {
            if (openTrigger === trigger) {
                return;
            }
            var otherId = openTrigger.getAttribute('aria-controls');
            var otherPanel = otherId ? document.getElementById(otherId) : null;
            closeRow(openTrigger, otherPanel);
        });

        trigger.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        if (panel) {
            panel.hidden = false;
        }
    }

    function toggleRow(trigger, table) {
        var panelId = trigger.getAttribute('aria-controls');
        var panel = panelId ? document.getElementById(panelId) : null;
        var isOpen = trigger.classList.contains('is-open');

        if (isOpen) {
            closeRow(trigger, panel);
            return;
        }

        openRow(trigger, panel, table);
    }

    function closeAllInTable(table) {
        table.querySelectorAll('[data-admin-accordion-trigger].is-open').forEach(function (trigger) {
            var panelId = trigger.getAttribute('aria-controls');
            var panel = panelId ? document.getElementById(panelId) : null;
            closeRow(trigger, panel);
        });
    }

    document.querySelectorAll('table.admin-table--expandable').forEach(function (table) {
        table.addEventListener('click', function (event) {
            if (!isMobileAccordion()) {
                return;
            }

            if (event.target.closest('a, button, input, label, select, textarea, form')) {
                return;
            }

            var trigger = event.target.closest('[data-admin-accordion-trigger]');
            if (!trigger || !table.contains(trigger)) {
                return;
            }

            event.preventDefault();
            toggleRow(trigger, table);
        });

        table.addEventListener('keydown', function (event) {
            if (!isMobileAccordion()) {
                return;
            }

            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            var trigger = event.target.closest('[data-admin-accordion-trigger]');
            if (!trigger || event.target !== trigger) {
                return;
            }

            event.preventDefault();
            toggleRow(trigger, table);
        });
    });

    function onViewportChange() {
        if (isMobileAccordion()) {
            return;
        }

        document.querySelectorAll('table.admin-table--expandable').forEach(closeAllInTable);
    }

    if (typeof mobileMq.addEventListener === 'function') {
        mobileMq.addEventListener('change', onViewportChange);
    } else if (typeof mobileMq.addListener === 'function') {
        mobileMq.addListener(onViewportChange);
    }
})();

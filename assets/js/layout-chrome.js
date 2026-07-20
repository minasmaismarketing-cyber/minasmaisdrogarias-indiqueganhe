/**
 * Layout chrome — mobile vs desktop (Sprint 4.5)
 * <992px ou PWA iPhone standalone → apenas bottom-nav
 */
(function () {
    function isStandalone() {
        try {
            if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
                return true;
            }
        } catch (e) { /* ignore */ }
        return window.navigator.standalone === true;
    }

    function isPhoneLike() {
        var w = Math.min(window.screen.width || 0, window.screen.height || 0);
        return w > 0 && w <= 500;
    }

    function syncMobileChrome() {
        var narrow = false;
        try {
            narrow = window.matchMedia('(max-width: 991.98px)').matches;
        } catch (e) {
            narrow = window.innerWidth < 992;
        }

        var mobile = narrow || (isStandalone() && isPhoneLike());
        document.documentElement.classList.toggle('is-mobile-chrome', mobile);
        if (document.body) {
            document.body.classList.toggle('is-mobile-chrome', mobile);
        }
    }

    syncMobileChrome();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncMobileChrome);
    }

    window.addEventListener('resize', syncMobileChrome);
    window.addEventListener('orientationchange', syncMobileChrome);

    try {
        if (window.matchMedia) {
            var mqWidth = window.matchMedia('(max-width: 991.98px)');
            var mqStand = window.matchMedia('(display-mode: standalone)');
            var onChange = function () { syncMobileChrome(); };
            if (typeof mqWidth.addEventListener === 'function') {
                mqWidth.addEventListener('change', onChange);
                mqStand.addEventListener('change', onChange);
            } else if (typeof mqWidth.addListener === 'function') {
                mqWidth.addListener(onChange);
                mqStand.addListener(onChange);
            }
        }
    } catch (e) { /* ignore */ }
})();

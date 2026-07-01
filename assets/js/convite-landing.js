/**
 * Landing /convite — AppsFlyer Smart Script V2 + fallback APP_DOWNLOAD_URL
 */
(function () {
    'use strict';

    function readRefFromUrl() {
        try {
            return new URLSearchParams(window.location.search).get('ref') || '';
        } catch (error) {
            return '';
        }
    }

    function applyFallback(button, fallbackUrl) {
        if (!button || !fallbackUrl || fallbackUrl === '#') {
            return;
        }

        button.setAttribute('href', fallbackUrl);
        button.setAttribute('target', '_blank');
        button.setAttribute('rel', 'noopener noreferrer');
        button.removeAttribute('aria-disabled');
    }

    function waitForSmartScript(maxAttempts, intervalMs) {
        return new Promise(function (resolve) {
            var attempts = 0;

            function check() {
                if (
                    window.AF_SMART_SCRIPT &&
                    typeof window.AF_SMART_SCRIPT.generateOneLinkURL === 'function'
                ) {
                    resolve(window.AF_SMART_SCRIPT);
                    return;
                }

                attempts += 1;

                if (attempts >= maxAttempts) {
                    resolve(null);
                    return;
                }

                setTimeout(check, intervalMs);
            }

            check();
        });
    }

    function buildAfParameters(config) {
        var refValue = config.ref || readRefFromUrl();

        return {
            mediaSource: { keys: [], defaultValue: config.mediaSource || '' },
            campaign: { keys: [], defaultValue: config.campaign || '' },
            deepLinkValue: { keys: [], defaultValue: config.deepLinkValue || 'indique' },
            afSub1: { keys: ['ref'], defaultValue: refValue },
            afSub2: { keys: [], defaultValue: config.usuarioId || '' },
            afSub3: { keys: [], defaultValue: config.campaign || '' },
            afSub4: { keys: [], defaultValue: config.deepLinkSub4 || 'indique_ganhe' },
            afSub5: { keys: [], defaultValue: config.deepLinkSub5 || 'homolog' },
        };
    }

    function initSmartScript() {
        var config = window.__CONVITE_AF__;

        if (!config || !config.enabled) {
            return;
        }

        var button = document.getElementById('convite-download-btn');
        var fallbackUrl = config.fallbackUrl || button?.getAttribute('data-fallback-url') || '#';

        if (!button) {
            return;
        }

        waitForSmartScript(40, 100).then(function (smartScript) {
            if (!smartScript) {
                applyFallback(button, fallbackUrl);
                return;
            }

            var result = smartScript.generateOneLinkURL({
                oneLinkURL: config.oneLinkURL,
                afParameters: buildAfParameters(config),
            });

            if (result && result.clickURL) {
                button.setAttribute('href', result.clickURL);
                button.setAttribute('target', '_blank');
                button.setAttribute('rel', 'noopener noreferrer');
                button.removeAttribute('aria-disabled');
                return;
            }

            applyFallback(button, fallbackUrl);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSmartScript);
    } else {
        initSmartScript();
    }
})();

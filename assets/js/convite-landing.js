/**
 * Landing /convite — AppsFlyer Smart Script V2 + fallback APP_DOWNLOAD_URL
 *
 * Toda a URL do OneLink é gerada exclusivamente por
 * window.AF_SMART_SCRIPT.generateOneLinkURL() — sem montagem manual.
 *
 * Deferred deep linking: deep_link_value + deep_link_sub1–5 (afCustom).
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

    /**
     * pid / c / deep_link_value + deep_link_sub1–5 via afCustom.
     * ref (?ref=) → deep_link_sub1; deep_link_value fixo = indique.
     */
    function buildAfParameters(config) {
        var refFallback = readRefFromUrl();
        var campaign = config.campaign || 'Indique e Ganhe Minas Mais';

        var afCustom = [
            {
                paramKey: 'deep_link_sub1',
                keys: ['ref'],
                defaultValue: refFallback,
            },
        ];

        if (config.deepLinkSub2) {
            afCustom.push({
                paramKey: 'deep_link_sub2',
                keys: [],
                defaultValue: String(config.deepLinkSub2),
            });
        }

        afCustom.push(
            {
                paramKey: 'deep_link_sub3',
                keys: [],
                defaultValue: campaign,
            },
            {
                paramKey: 'deep_link_sub4',
                keys: [],
                defaultValue: config.deepLinkSub4 || 'indique_ganhe',
            },
            {
                paramKey: 'deep_link_sub5',
                keys: [],
                defaultValue: config.deepLinkSub5 || 'homolog',
            }
        );

        return {
            mediaSource: {
                keys: [],
                defaultValue: config.mediaSource || 'User_invite',
            },
            campaign: {
                keys: [],
                defaultValue: campaign,
            },
            deepLinkValue: {
                keys: [],
                defaultValue: config.deepLinkValue || 'indique',
            },
            afCustom: afCustom,
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

            var result;

            try {
                result = smartScript.generateOneLinkURL({
                    oneLinkURL: config.oneLinkURL,
                    afParameters: buildAfParameters(config),
                });
            } catch (error) {
                applyFallback(button, fallbackUrl);
                return;
            }

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

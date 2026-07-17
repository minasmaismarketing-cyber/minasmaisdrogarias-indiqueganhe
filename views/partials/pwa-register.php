<?php declare(strict_types=1); ?>
<script>
(() => {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    const host = location.hostname;
    const isLocal = host === 'localhost' || host === '127.0.0.1';
    if (location.protocol !== 'https:' && !isLocal) {
        return;
    }

    const swUrl = <?= json_encode(url('/sw.js'), JSON_UNESCAPED_SLASHES) ?>;
    window.addEventListener('load', () => {
        navigator.serviceWorker.register(swUrl).catch(() => {});
    });
})();
</script>

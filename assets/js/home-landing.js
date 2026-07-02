(() => {
    const root = document.querySelector('.mm-home');
    if (!root) return;

    const revealItems = [
        root.querySelector('.mm-home__hero-content'),
        root.querySelector('.mm-home__summary'),
        ...root.querySelectorAll('.mm-home__step'),
        root.querySelector('.mm-home__app-card'),
        root.querySelector('.mm-home__trust-grid')
    ].filter(Boolean);

    if (!('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.16,
        rootMargin: '0px 0px -40px 0px'
    });

    revealItems.forEach((item, index) => {
        item.style.transitionDelay = `${Math.min(index * 70, 280)}ms`;
        observer.observe(item);
    });

    root.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const target = document.querySelector(link.getAttribute('href'));
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
})();

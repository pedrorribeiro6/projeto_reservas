document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('.top-nav');
    if (!nav) return;

    const navLinks = nav.querySelector('.nav-links');
    if (!navLinks) return;

    // Create hamburger button
    const hamburger = document.createElement('button');
    hamburger.className = 'hamburger-btn';
    hamburger.setAttribute('aria-label', 'Abrir menu');
    hamburger.setAttribute('aria-expanded', 'false');
    hamburger.innerHTML = '☰';

    // Insert hamburger after nav-brand
    const brand = nav.querySelector('.nav-brand');
    if (brand) {
        brand.after(hamburger);
    } else {
        nav.prepend(hamburger);
    }

    // Toggle menu
    hamburger.addEventListener('click', () => {
        const isOpen = navLinks.classList.toggle('mobile-open');
        hamburger.innerHTML = isOpen ? '✕' : '☰';
        hamburger.setAttribute('aria-expanded', isOpen.toString());
    });

    // Close menu when clicking a link
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('mobile-open');
            hamburger.innerHTML = '☰';
            hamburger.setAttribute('aria-expanded', 'false');
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!nav.contains(e.target) && navLinks.classList.contains('mobile-open')) {
            navLinks.classList.remove('mobile-open');
            hamburger.innerHTML = '☰';
            hamburger.setAttribute('aria-expanded', 'false');
        }
    });
});

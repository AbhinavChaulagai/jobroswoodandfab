/**
 * main.js — Jobros Wood & Fab
 * Vanilla JS only. Handles:
 *  - Mobile navigation toggle
 *  - Contact form async submission (fetch API)
 *  - Smooth scroll-reveal animations
 */

'use strict';

/* ==========================================================================
   1. Mobile Navigation Toggle
   ========================================================================== */
(function initMobileNav() {
    const toggle = document.getElementById('navToggle');
    const nav    = document.getElementById('primaryNav');

    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
        const isOpen = nav.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(isOpen));
    });

    // Close nav when a link inside it is clicked (single-page feel)
    nav.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            nav.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });

    // Close nav on outside click
    document.addEventListener('click', function (e) {
        if (!nav.contains(e.target) && !toggle.contains(e.target)) {
            nav.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
})();


/* ==========================================================================
   2. Contact Form — Async Submission
   ========================================================================== */
(function initContactForm() {
    const form     = document.getElementById('contactForm');
    const feedback = document.getElementById('form-feedback');
    const submitBtn = document.getElementById('submitBtn');

    if (!form || !feedback) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Clear previous feedback
        feedback.innerHTML = '';
        feedback.className = '';

        // Disable button to prevent double-submit
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending…';

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method:  'POST',
                body:    formData,
                headers: { 'Accept': 'application/json' },
            });

            let data;
            try {
                data = await response.json();
            } catch {
                throw new Error('Unexpected server response. Please try again.');
            }

            if (data.success) {
                // Show success message
                feedback.className  = 'feedback-success';
                feedback.innerHTML  = '<strong>Message sent!</strong> ' + escapeHtml(data.message);
                form.reset();

                // Scroll feedback into view
                feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                // Show validation errors
                let html = '<strong>Please correct the following:</strong>';
                if (Array.isArray(data.errors) && data.errors.length) {
                    html += '<ul>';
                    data.errors.forEach(function (err) {
                        html += '<li>' + escapeHtml(err) + '</li>';
                    });
                    html += '</ul>';
                } else {
                    html += ' ' + escapeHtml(data.message || 'An error occurred.');
                }
                feedback.className = 'feedback-error';
                feedback.innerHTML = html;
                feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

        } catch (err) {
            feedback.className = 'feedback-error';
            feedback.innerHTML = '<strong>Error:</strong> ' + escapeHtml(err.message);
        } finally {
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Send Message';
        }
    });

    /**
     * Minimal HTML escaping — prevents XSS when reflecting server messages.
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();


/* ==========================================================================
   3. Scroll-Reveal Animations
   Uses IntersectionObserver to fade-in elements as they enter the viewport.
   Falls back gracefully in older browsers (elements are just always visible).
   ========================================================================== */
(function initScrollReveal() {
    if (!('IntersectionObserver' in window)) return;

    // Add the base class to elements we want to animate
    const targets = document.querySelectorAll(
        '.product-card, .value-card, .process-step, .trust-item, .about-blurb-text, .about-blurb-img'
    );

    // Inject keyframe styles once
    if (targets.length > 0 && !document.getElementById('scroll-reveal-styles')) {
        const style = document.createElement('style');
        style.id    = 'scroll-reveal-styles';
        style.textContent = `
            .sr-hidden {
                opacity: 0;
                transform: translateY(24px);
                transition: opacity 0.55s ease, transform 0.55s ease;
            }
            .sr-visible {
                opacity: 1;
                transform: translateY(0);
            }
        `;
        document.head.appendChild(style);
    }

    // Mark elements as hidden initially
    targets.forEach(function (el, i) {
        el.classList.add('sr-hidden');
        // Stagger items within a grid row
        el.style.transitionDelay = (i % 3) * 80 + 'ms';
    });

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('sr-visible');
                observer.unobserve(entry.target); // animate once only
            }
        });
    }, {
        rootMargin: '0px 0px -60px 0px',
        threshold:  0.1,
    });

    targets.forEach(function (el) { observer.observe(el); });
})();


/* ==========================================================================
   4. Product Image Gallery (product.php)
   ========================================================================== */
(function () {
    var mainImg = document.getElementById('galleryMain');
    var thumbs  = document.querySelectorAll('.g-thumb');
    var prev    = document.getElementById('gPrev');
    var next    = document.getElementById('gNext');
    var gallery = window.GALLERY || [];

    if (!mainImg || gallery.length === 0) return;

    var cur = 0;
    mainImg.style.transition = 'opacity 0.15s ease';

    function goTo(n) {
        cur = (n + gallery.length) % gallery.length;
        mainImg.style.opacity = '0';
        setTimeout(function () {
            mainImg.src = gallery[cur];
            mainImg.style.opacity = '1';
        }, 150);
        thumbs.forEach(function (t, i) {
            t.classList.toggle('active', i === cur);
        });
    }

    thumbs.forEach(function (t, i) {
        t.addEventListener('click', function () { goTo(i); });
    });
    if (prev) prev.addEventListener('click', function () { goTo(cur - 1); });
    if (next) next.addEventListener('click', function () { goTo(cur + 1); });

    // Keyboard
    document.addEventListener('keydown', function (e) {
        if (!document.getElementById('gallery')) return;
        if (e.key === 'ArrowLeft')  goTo(cur - 1);
        if (e.key === 'ArrowRight') goTo(cur + 1);
    });

    // Touch swipe
    var tx = 0;
    mainImg.addEventListener('touchstart', function (e) { tx = e.touches[0].clientX; }, { passive: true });
    mainImg.addEventListener('touchend',   function (e) {
        var d = e.changedTouches[0].clientX - tx;
        if (Math.abs(d) > 50) goTo(cur + (d < 0 ? 1 : -1));
    }, { passive: true });
})();

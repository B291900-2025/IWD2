// animate.js — scroll-triggered animations for ProtExplorer
// Adds .animate-on-scroll to all cards and triggers .visible
// when they enter the viewport using IntersectionObserver

document.addEventListener('DOMContentLoaded', function() {

    // Add animate class to all cards and page-header elements
    var elements = document.querySelectorAll(
        '.card, .run-card, .structure-card, .seq-group, ' +
        '.stat-box, .audience-card, .arch-box, .contact-info-box'
    );

    elements.forEach(function(el) {
        el.classList.add('animate-on-scroll');
    });

    // Use IntersectionObserver to trigger animations on scroll
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // Stop observing once visible
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold:  0.1,
            rootMargin: '0px 0px -40px 0px'
        });

        elements.forEach(function(el) {
            observer.observe(el);
        });

    } else {
        // Fallback for older browsers — show everything immediately
        elements.forEach(function(el) {
            el.classList.add('visible');
        });
    }
});

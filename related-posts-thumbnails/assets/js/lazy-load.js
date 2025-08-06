/**
 * Lazy Load Script
 *
 * This script is responsible for lazy loading images and background images
 * for elements with the classes "rpt-lazyload" and "lazy-load". It uses
 * the IntersectionObserver API to efficiently load images as they enter
 * the viewport.
 */

document.addEventListener("DOMContentLoaded", function() {
    var lazyloadElements = document.querySelectorAll(".rpt-lazyload, .lazy-load");

    if ("IntersectionObserver" in window) {
        var imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var element = entry.target;

                    if (element.classList.contains('rpt-lazyload')) {
                        // Handle background images
                        var imageUrl = element.dataset.bg;
                        element.style.backgroundImage = 'url(' + imageUrl + ')';
                    } else if (element.classList.contains('lazy-load')) {
                        // Handle <img> tags
                        var imageUrl = element.dataset.src;
                        element.src = imageUrl;
                    }

                    element.classList.remove("rpt-lazyload", "lazy-load");
                    observer.unobserve(element);
                }
            });
        });

        lazyloadElements.forEach(function(element) {
            imageObserver.observe(element);
        });
    }
});

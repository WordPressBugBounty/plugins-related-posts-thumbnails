/**
 * Lazy Load Script
 *
 * This script is responsible for lazy loading images and background images
 * for elements with the classes "rpt-lazyload" and "lazy-load". It uses
 * the IntersectionObserver API to efficiently load images as they enter
 * the viewport.
 */

document.addEventListener("DOMContentLoaded", function() {
	const lazyloadElements = document.querySelectorAll(".rpt-lazyload, .lazy-load");

	if ("IntersectionObserver" in window) { // NOSONAR — WordPress frontend uses window (javascript:S7764)
		const imageObserver = new IntersectionObserver(function(entries, observer) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting) {
					const element = entry.target;
					let imageUrl = "";

					if (element.classList.contains("rpt-lazyload")) {
						imageUrl = element.dataset.bg;
						element.style.backgroundImage = "url(" + imageUrl + ")";
					} else if (element.classList.contains("lazy-load")) {
						imageUrl = element.dataset.src;
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

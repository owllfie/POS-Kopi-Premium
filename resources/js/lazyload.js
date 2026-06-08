/**
 * Global Lazy Loading System
 * Role: Senior Full-Stack Developer / Performance Optimization Expert
 *
 * This utility handles:
 * 1. Automatic native lazy loading injection (`loading="lazy"`) for <img> and <iframe>.
 * 2. Intersection Observer for deferred loading of images, videos, iframes, and dynamic database-driven components.
 * 3. Dynamic HTML loading via AJAX/fetch for sections tagged with `data-lazy-url`.
 * 4. Layout shift (CLS) prevention using aspect-ratio placeholders and skeleton states.
 */

class LazyLoadManager {
    constructor(options = {}) {
        this.options = Object.assign({
            root: null,
            rootMargin: '200px', // Start loading 200px before entering viewport
            threshold: 0.01
        }, options);

        this.observer = null;
    }

    /**
     * Initialize the lazy loading system.
     */
    init() {
        // 1. Setup native lazy loading auto-injector for images and iframes
        this.setupNativeLazyLoading();

        // 2. Setup Intersection Observer for deferred components & assets
        this.setupIntersectionObserver();

        // 3. Scan the document for target elements
        this.scan();

        // 4. Setup a mutation observer to auto-observe new elements injected into the DOM
        this.setupMutationObserver();
    }

    /**
     * Auto-injects loading="lazy" into static images/iframes that don't have it.
     * Respects developer-defined loading="eager" or loading="lazy".
     */
    setupNativeLazyLoading() {
        const injectLazy = (el) => {
            if ((el.tagName === 'IMG' || el.tagName === 'IFRAME') && !el.hasAttribute('loading')) {
                // If it doesn't have class eager-load or parent has above-the-fold, set lazy
                if (!el.closest('.above-the-fold') && !el.classList.contains('eager-load') && el.getAttribute('loading') !== 'eager') {
                    el.setAttribute('loading', 'lazy');
                } else {
                    el.setAttribute('loading', 'eager');
                }
            }
        };

        // Apply initially
        document.querySelectorAll('img, iframe').forEach(injectLazy);
    }

    /**
     * Setup the main IntersectionObserver instance.
     */
    setupIntersectionObserver() {
        if (!('IntersectionObserver' in window)) {
            // Fallback for older browsers: Load everything immediately
            this.loadAllFallback();
            return;
        }

        this.observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    this.loadElement(target);
                    observer.unobserve(target); // Unobserve to save memory/performance
                }
            });
        }, this.options);
    }

    /**
     * Scan the DOM for lazy elements and observe them.
     */
    scan() {
        const selector = '[data-src], [data-lazy-url], .lazy-load-section, [data-lazy-component]';
        document.querySelectorAll(selector).forEach(el => {
            if (this.observer) {
                this.observer.observe(el);
            } else {
                this.loadElement(el);
            }
        });
    }

    /**
     * Setup a MutationObserver to automatically discover and observe dynamically added elements.
     */
    setupMutationObserver() {
        const mutationObserver = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType !== Node.ELEMENT_NODE) return;

                    // Match direct element
                    if (node.matches && node.matches('[data-src], [data-lazy-url], .lazy-load-section, [data-lazy-component]')) {
                        if (this.observer) this.observer.observe(node);
                        else this.loadElement(node);
                    }

                    // Match descendants
                    if (node.querySelectorAll) {
                        node.querySelectorAll('[data-src], [data-lazy-url], .lazy-load-section, [data-lazy-component]').forEach(el => {
                            if (this.observer) this.observer.observe(el);
                            else this.loadElement(el);
                        });
                    }

                    // Ensure native lazy loading is applied to new images/iframes
                    if (node.tagName === 'IMG' || node.tagName === 'IFRAME') {
                        if (!node.hasAttribute('loading')) node.setAttribute('loading', 'lazy');
                    }
                    if (node.querySelectorAll) {
                        node.querySelectorAll('img, iframe').forEach(el => {
                            if (!el.hasAttribute('loading')) el.setAttribute('loading', 'lazy');
                        });
                    }
                });
            });
        });

        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Load the target lazy element.
     * @param {HTMLElement} el 
     */
    loadElement(el) {
        el.classList.add('lazy-loading');

        // Handle Image / Iframe source swap
        if (el.tagName === 'IMG' || el.tagName === 'IFRAME') {
            this.swapSource(el);
            this.handleLoadEvent(el);
            return;
        }

        // Handle Video loading
        if (el.tagName === 'VIDEO') {
            this.loadVideo(el);
            this.handleLoadEvent(el);
            return;
        }

        // Handle Dynamic components fetching remote URL HTML
        if (el.hasAttribute('data-lazy-url')) {
            this.fetchDynamicContent(el);
            return;
        }

        // Handle generic .lazy-load-section / [data-lazy-component] (Custom JS / AlpineJS integration)
        this.triggerCustomLazyEvents(el);
    }

    /**
     * Swap data-src to src and data-srcset to srcset
     * @param {HTMLElement} el 
     */
    swapSource(el) {
        if (el.hasAttribute('data-src')) {
            el.src = el.getAttribute('data-src');
            el.removeAttribute('data-src');
        }
        if (el.hasAttribute('data-srcset')) {
            el.srcset = el.getAttribute('data-srcset');
            el.removeAttribute('data-srcset');
        }
    }

    /**
     * Setup video element source attributes and load it.
     * @param {HTMLVideoElement} video 
     */
    loadVideo(video) {
        if (video.hasAttribute('data-poster')) {
            video.poster = video.getAttribute('data-poster');
            video.removeAttribute('data-poster');
        }

        // Handle nested <source> tags
        const sources = video.querySelectorAll('source');
        if (sources.length > 0) {
            sources.forEach(source => {
                this.swapSource(source);
            });
        }

        this.swapSource(video);
        video.load();
    }

    /**
     * Listen to the load event to toggle CSS transition classes.
     * @param {HTMLElement} el 
     */
    handleLoadEvent(el) {
        const onLoad = () => {
            el.classList.remove('lazy-loading');
            el.classList.add('lazy-loaded');
            el.dispatchEvent(new CustomEvent('lazy-loaded', { bubbles: true }));
            el.removeEventListener('load', onLoad);
            el.removeEventListener('error', onError);
        };

        const onError = () => {
            el.classList.remove('lazy-loading');
            el.classList.add('lazy-failed');
            el.dispatchEvent(new CustomEvent('lazy-error', { bubbles: true }));
            el.removeEventListener('load', onLoad);
            el.removeEventListener('error', onError);
        };

        if (el.tagName === 'IMG') {
            if (el.complete && el.naturalWidth !== 0) {
                // Already loaded
                onLoad();
                return;
            }
        }

        el.addEventListener('load', onLoad);
        el.addEventListener('error', onError);
    }

    /**
     * Fetches dynamic HTML from server and replaces container contents.
     * @param {HTMLElement} el 
     */
    async fetchDynamicContent(el) {
        const url = el.getAttribute('data-lazy-url');
        if (!url) return;

        // Render skeleton loader if it doesn't already have children or is empty
        if (!el.innerHTML.trim()) {
            el.innerHTML = this.getSkeletonTemplate(el.getAttribute('data-skeleton-type') || 'generic');
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

            const html = await response.text();
            el.innerHTML = html;

            // Remove loading states & trigger CSS animations
            el.classList.remove('lazy-loading');
            el.classList.add('lazy-loaded');

            // Dispatch custom events for standard JS and AlpineJS listeners
            const customEvent = new CustomEvent('lazy-loaded', {
                bubbles: true,
                detail: { url, success: true }
            });
            el.dispatchEvent(customEvent);

            // Execute scripts inside fetched HTML if any
            el.querySelectorAll('script').forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });

        } catch (error) {
            console.error('[LazyLoadManager] Failed to fetch dynamic content:', error);
            el.classList.remove('lazy-loading');
            el.classList.add('lazy-failed');
            el.innerHTML = `
                <div class="p-6 text-center border border-rose-100 rounded-2xl bg-rose-50/50 text-rose-800">
                    <p class="font-medium text-sm">Gagal memuat data.</p>
                    <button class="mt-2.5 px-4 py-2 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow-md"
                            onclick="window.LazyLoad.retry(this.closest('[data-lazy-url]'))">
                        Coba Lagi
                    </button>
                </div>
            `;
            el.dispatchEvent(new CustomEvent('lazy-error', {
                bubbles: true,
                detail: { url, error }
            }));
        }
    }

    /**
     * Dispatch event triggers for Custom scripts and AlpineJS
     * @param {HTMLElement} el 
     */
    triggerCustomLazyEvents(el) {
        const event = new CustomEvent('lazyload', {
            bubbles: true,
            cancelable: true
        });
        el.dispatchEvent(event);

        // Remove loading state and mark as loaded
        el.classList.remove('lazy-loading');
        el.classList.add('lazy-loaded');
    }

    /**
     * Fallback for older browsers (no IntersectionObserver)
     */
    loadAllFallback() {
        const selector = '[data-src], [data-lazy-url], .lazy-load-section, [data-lazy-component]';
        document.querySelectorAll(selector).forEach(el => this.loadElement(el));
    }

    /**
     * Retries dynamic content loading manually
     * @param {HTMLElement} el 
     */
    retry(el) {
        if (el && el.hasAttribute('data-lazy-url')) {
            this.loadElement(el);
        }
    }

    /**
     * Get skeleton templates based on type
     * @param {string} type 
     * @returns {string} HTML string
     */
    getSkeletonTemplate(type) {
        if (type === 'table') {
            return `
                <div class="w-full space-y-4 animate-pulse py-2">
                    <div class="h-10 bg-coffee-latte rounded-xl w-full skeleton-loader"></div>
                    <div class="space-y-3">
                        <div class="h-8 bg-coffee-latte rounded-xl w-full skeleton-loader opacity-85"></div>
                        <div class="h-8 bg-coffee-latte rounded-xl w-full skeleton-loader opacity-70"></div>
                        <div class="h-8 bg-coffee-latte rounded-xl w-full skeleton-loader opacity-50"></div>
                    </div>
                </div>
            `;
        }

        if (type === 'card') {
            return `
                <div class="coffee-card rounded-3xl p-5 space-y-4 animate-pulse">
                    <div class="aspect-video w-full rounded-2xl skeleton-loader"></div>
                    <div class="space-y-2">
                        <div class="h-5 bg-coffee-latte rounded-md w-2/3 skeleton-loader"></div>
                        <div class="h-4 bg-coffee-latte rounded-md w-1/2 skeleton-loader"></div>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <div class="h-6 bg-coffee-latte rounded-md w-1/4 skeleton-loader"></div>
                        <div class="h-8 bg-coffee-latte rounded-xl w-1/3 skeleton-loader"></div>
                    </div>
                </div>
            `;
        }

        // Default / Generic skeleton
        return `
            <div class="w-full p-6 space-y-4 animate-pulse">
                <div class="h-6 bg-coffee-latte rounded-md w-1/3 skeleton-loader"></div>
                <div class="h-4 bg-coffee-latte rounded-md w-full skeleton-loader"></div>
                <div class="h-4 bg-coffee-latte rounded-md w-5/6 skeleton-loader"></div>
            </div>
        `;
    }
}

// Instantiate and expose globally
window.LazyLoad = new LazyLoadManager();
document.addEventListener('DOMContentLoaded', () => {
    window.LazyLoad.init();
});

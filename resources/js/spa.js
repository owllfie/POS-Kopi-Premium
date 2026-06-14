/**
 * Kopi Premium - SPA Client-Side Router
 * Enables instant transitions and asynchronous content loading.
 */

// Save native DOM and Window APIs before overriding
const nativeWindowAddEventListener = window.addEventListener;
const nativeDocumentAddEventListener = document.addEventListener;
const nativeWindowRemoveEventListener = window.removeEventListener;
const nativeDocumentRemoveEventListener = document.removeEventListener;

const nativeSetInterval = window.setInterval;
const nativeClearInterval = window.clearInterval;
const nativeSetTimeout = window.setTimeout;
const nativeClearTimeout = window.clearTimeout;

// Track active page-level event listeners and timers
const activeListeners = [];
const activeIntervals = [];
const activeTimeouts = [];

window.addEventListener = function(type, listener, options) {
    activeListeners.push({ target: window, type, listener, options });
    nativeWindowAddEventListener.call(window, type, listener, options);
};

document.addEventListener = function(type, listener, options) {
    activeListeners.push({ target: document, type, listener, options });
    nativeDocumentAddEventListener.call(document, type, listener, options);
};

window.removeEventListener = function(type, listener, options) {
    const index = activeListeners.findIndex(l => l.target === window && l.type === type && l.listener === listener);
    if (index !== -1) activeListeners.splice(index, 1);
    nativeWindowRemoveEventListener.call(window, type, listener, options);
};

document.removeEventListener = function(type, listener, options) {
    const index = activeListeners.findIndex(l => l.target === document && l.type === type && l.listener === listener);
    if (index !== -1) activeListeners.splice(index, 1);
    nativeDocumentRemoveEventListener.call(document, type, listener, options);
};

window.setInterval = function(handler, timeout, ...args) {
    const id = nativeSetInterval.call(window, handler, timeout, ...args);
    activeIntervals.push(id);
    return id;
};

window.clearInterval = function(id) {
    const index = activeIntervals.indexOf(id);
    if (index !== -1) activeIntervals.splice(index, 1);
    nativeClearInterval.call(window, id);
};

window.setTimeout = function(handler, timeout, ...args) {
    const id = nativeSetTimeout.call(window, handler, timeout, ...args);
    activeTimeouts.push(id);
    return id;
};

window.clearTimeout = function(id) {
    const index = activeTimeouts.indexOf(id);
    if (index !== -1) activeTimeouts.splice(index, 1);
    nativeClearTimeout.call(window, id);
};

// Expose native functions globally for persistent layout elements (e.g. live clock)
window.nativeSetInterval = nativeSetInterval;
window.nativeClearInterval = nativeClearInterval;
window.nativeSetTimeout = nativeSetTimeout;
window.nativeClearTimeout = nativeClearTimeout;

function clearPageStates() {
    // 1. Clear active event listeners
    activeListeners.forEach(({ target, type, listener, options }) => {
        if (target === window) {
            nativeWindowRemoveEventListener.call(window, type, listener, options);
        } else if (target === document) {
            nativeDocumentRemoveEventListener.call(document, type, listener, options);
        }
    });
    activeListeners.length = 0;

    // 2. Clear active intervals
    activeIntervals.forEach(id => nativeClearInterval.call(window, id));
    activeIntervals.length = 0;

    // 3. Clear active timeouts
    activeTimeouts.forEach(id => nativeClearTimeout.call(window, id));
    activeTimeouts.length = 0;
}

// Inject styles dynamically for progress bar and loading states
const styleEl = document.createElement('style');
styleEl.textContent = `
    #spa-progress-bar {
        position: fixed;
        top: 0;
        left: 0;
        height: 3px;
        background: #d97706; /* amber-600 / Gold-coffee */
        z-index: 99999;
        width: 0%;
        opacity: 0;
        transition: width 0.4s cubic-bezier(0.08, 0.82, 0.17, 1), opacity 0.3s ease;
        box-shadow: 0 0 10px #f59e0b, 0 0 5px #d97706;
    }

    main.spa-loading {
        opacity: 0.45;
        filter: blur(1.5px);
        transform: translateY(4px);
        pointer-events: none;
        transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1), filter 0.2s ease, transform 0.2s ease;
    }

    .spa-content-enter {
        opacity: 0 !important;
        transform: translateY(8px) !important;
        filter: blur(2px) !important;
    }

    .spa-content-enter-active {
        opacity: 1;
        transform: translateY(0);
        filter: none;
        transition: opacity 0.35s cubic-bezier(0.34, 1.3, 0.64, 1), transform 0.35s cubic-bezier(0.34, 1.3, 0.64, 1), filter 0.3s ease;
    }

    .spa-nav-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.8;
    }

    .spa-nav-loading::after {
        content: '';
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 14px;
        height: 14px;
        border: 2px solid rgba(212, 175, 55, 0.3);
        border-top-color: #d4af37;
        border-radius: 50%;
        animation: spa-spin 0.6s linear infinite;
    }

    @keyframes spa-spin {
        to { transform: translateY(-50%) rotate(360deg); }
    }
`;
document.head.appendChild(styleEl);

// Setup progress bar element
const progressBar = document.createElement('div');
progressBar.id = 'spa-progress-bar';
document.body.appendChild(progressBar);

let progressInterval = null;

function startProgress() {
    progressBar.style.background = '#d97706'; // Reset color to gold
    progressBar.style.width = '0%';
    progressBar.style.opacity = '1';
    nativeClearInterval(progressInterval);
    
    let width = 0;
    progressInterval = nativeSetInterval(() => {
        if (width < 85) {
            width += Math.random() * 5 + 1.5;
            progressBar.style.width = `${width}%`;
        }
    }, 150);
}

function completeProgress() {
    nativeClearInterval(progressInterval);
    progressBar.style.width = '100%';
    nativeSetTimeout(() => {
        progressBar.style.opacity = '0';
        nativeSetTimeout(() => {
            progressBar.style.width = '0%';
        }, 300);
    }, 200);
}

function failProgress() {
    nativeClearInterval(progressInterval);
    progressBar.style.background = '#ef4444'; // Red for error
    progressBar.style.width = '100%';
    nativeSetTimeout(() => {
        progressBar.style.opacity = '0';
        nativeSetTimeout(() => {
            progressBar.style.width = '0%';
        }, 300);
    }, 200);
}

/**
 * Loads a page content via AJAX and replaces specific sections.
 */
async function loadPage(url, pushToHistory = true, clickedLink = null) {
    const appRootEl = document.getElementById('app-root');
    const pageStylesEl = document.getElementById('page-styles');
    const pageScriptsEl = document.getElementById('page-scripts');

    // Add loading states
    const mainEl = appRootEl ? appRootEl.querySelector('main') : null;
    if (mainEl) mainEl.classList.add('spa-loading');
    if (clickedLink) clickedLink.classList.add('spa-nav-loading');
    startProgress();

    try {
        const response = await fetch(url, {
            cache: 'no-cache',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-SPA-Request': 'true',
                'Accept': 'text/html',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Extract key components
        const newAppRoot = doc.getElementById('app-root');
        const newPageStyles = doc.getElementById('page-styles');
        const newPageScripts = doc.getElementById('page-scripts');

        const finalUrl = response.url || url;

        // Fallback to standard page load if structure is different
        if (!newAppRoot) {
            window.location.href = finalUrl;
            return;
        }

        // Update URL and browser history
        if (pushToHistory) {
            history.pushState({ spa: true, url: finalUrl }, '', finalUrl);
        }

        // Update Document Title
        document.title = doc.title || document.title;

        // Update CSS Styles
        if (pageStylesEl && newPageStyles) {
            pageStylesEl.innerHTML = newPageStyles.innerHTML;
        }

        // Update App Root Container with Premium Enter Animation
        if (appRootEl && newAppRoot) {
            appRootEl.classList.remove('spa-loading');
            appRootEl.classList.add('spa-content-enter');
            
            // Clean up old event listeners and timers before swapping DOM
            clearPageStates();

            // Swap class name
            appRootEl.className = newAppRoot.className;
            // Swap all attributes (including x-data)
            Array.from(appRootEl.attributes).forEach(attr => appRootEl.removeAttribute(attr.name));
            Array.from(newAppRoot.attributes).forEach(attr => appRootEl.setAttribute(attr.name, attr.value));
            
            appRootEl.innerHTML = newAppRoot.innerHTML;
            
            const newMainEl = appRootEl.querySelector('main');
            if (newMainEl) {
                newMainEl.scrollTop = 0; // Reset scroll position
            }

            // Trigger reflow & animation frames
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    appRootEl.classList.remove('spa-content-enter');
                    appRootEl.classList.add('spa-content-enter-active');
                    
                    nativeSetTimeout(() => {
                        appRootEl.classList.remove('spa-content-enter-active');
                    }, 400);
                });
            });
        }

        // Temporarily mock event listeners to run DOMContentLoaded and load events immediately
        const originalDocAddEventListener = document.addEventListener;
        const originalWinAddEventListener = window.addEventListener;

        document.addEventListener = function (type, listener, options) {
            if (type === 'DOMContentLoaded') {
                try { listener(); } catch (e) { console.error('Error in dynamic DOMContentLoaded:', e); }
            } else {
                originalDocAddEventListener.call(document, type, listener, options);
            }
        };

        window.addEventListener = function (type, listener, options) {
            if (type === 'DOMContentLoaded' || type === 'load') {
                try { listener(); } catch (e) { console.error(`Error in dynamic ${type}:`, e); }
            } else {
                originalWinAddEventListener.call(window, type, listener, options);
            }
        };

        // Helper to execute scripts inside a container
        const executeScripts = (container) => {
            if (!container) return;
            const scripts = container.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                // Copy all attributes
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                // Copy inner JS content
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                // Replace old script with new script in-place to trigger execution
                if (oldScript.parentNode) {
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                } else {
                    document.body.appendChild(newScript);
                    newScript.remove();
                }
            });
        };

        // Update and execute scripts
        if (pageScriptsEl && newPageScripts) {
            pageScriptsEl.innerHTML = newPageScripts.innerHTML;
            executeScripts(pageScriptsEl);
        }

        // Also search and execute any script tags inside appRootEl (e.g. inline scripts in page views)
        if (appRootEl) {
            executeScripts(appRootEl);
        }

        // Restore original event listeners
        document.addEventListener = originalDocAddEventListener;
        window.addEventListener = originalWinAddEventListener;

        // Initialize Alpine.js on the new appRootEl content
        if (window.Alpine) {
            window.Alpine.initTree(appRootEl);
        }

        // Re-run LazyLoader scan
        if (window.LazyLoad) {
            window.LazyLoad.scan();
        }

        // Trigger custom global event for external scripts (e.g. to reset chart instances, table plugins)
        window.dispatchEvent(new CustomEvent('spa:navigated', { detail: { url: finalUrl } }));

        completeProgress();
    } catch (error) {
        console.error('[SPA Router] Navigation failed:', error);
        failProgress();
        // Fallback to normal page load
        window.location.href = url;
    } finally {
        if (appRootEl) appRootEl.classList.remove('spa-loading');
        if (clickedLink) clickedLink.classList.remove('spa-nav-loading');
    }
}

// Intercept all same-origin link clicks
nativeDocumentAddEventListener.call(document, 'click', (e) => {
    const link = e.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href) return;

    // Standard SPA boundary checks
    if (
        link.origin !== window.location.origin ||
        link.hasAttribute('download') ||
        link.getAttribute('target') === '_blank' ||
        href.startsWith('#') ||
        href.startsWith('javascript:') ||
        e.metaKey || e.ctrlKey || e.shiftKey || e.altKey ||
        link.classList.contains('no-spa') ||
        link.hasAttribute('data-no-spa')
    ) {
        return;
    }

    // If it is the current page, do nothing (or refresh)
    if (link.href === window.location.href) {
        e.preventDefault();
        return;
    }

    e.preventDefault();
    loadPage(link.href, true, link);
});

// Handle browser Back/Forward navigation
nativeWindowAddEventListener.call(window, 'popstate', (e) => {
    loadPage(window.location.href, false);
});

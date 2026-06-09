/**
 * Kopi Premium - SPA Client-Side Router
 * Enables instant transitions and asynchronous content loading.
 */

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
    clearInterval(progressInterval);
    
    let width = 0;
    progressInterval = setInterval(() => {
        if (width < 85) {
            width += Math.random() * 5 + 1.5;
            progressBar.style.width = `${width}%`;
        }
    }, 150);
}

function completeProgress() {
    clearInterval(progressInterval);
    progressBar.style.width = '100%';
    setTimeout(() => {
        progressBar.style.opacity = '0';
        setTimeout(() => {
            progressBar.style.width = '0%';
        }, 300);
    }, 200);
}

function failProgress() {
    clearInterval(progressInterval);
    progressBar.style.background = '#ef4444'; // Red for error
    progressBar.style.width = '100%';
    setTimeout(() => {
        progressBar.style.opacity = '0';
        setTimeout(() => {
            progressBar.style.width = '0%';
        }, 300);
    }, 200);
}

/**
 * Loads a page content via AJAX and replaces specific sections.
 */
async function loadPage(url, pushToHistory = true, clickedLink = null) {
    const mainEl = document.querySelector('main');
    const pageTitleEl = document.getElementById('page-title');
    const sidebarNavEl = document.getElementById('sidebar-nav');
    const pageStylesEl = document.getElementById('page-styles');
    const pageScriptsEl = document.getElementById('page-scripts');

    // Add loading states
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
        const newMain = doc.querySelector('main');
        const newPageTitle = doc.getElementById('page-title');
        const newSidebarNav = doc.getElementById('sidebar-nav');
        const newPageStyles = doc.getElementById('page-styles');
        const newPageScripts = doc.getElementById('page-scripts');

        const finalUrl = response.url || url;

        // Fallback to standard page load if structure is different
        if (!newMain) {
            window.location.href = finalUrl;
            return;
        }

        // Pause AlpineJS mutation observing and destroy old trees to prevent ReferenceErrors and memory leaks during DOM swap
        if (window.Alpine) {
            try {
                window.Alpine.stopObservingMutations();
                if (mainEl) window.Alpine.destroyTree(mainEl);
                if (sidebarNavEl) window.Alpine.destroyTree(sidebarNavEl);
            } catch (e) {
                console.warn('[SPA Router] Failed to clean up Alpine tree:', e);
            }
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

        // Update Page Title Header
        if (pageTitleEl && newPageTitle) {
            pageTitleEl.innerHTML = newPageTitle.innerHTML;
        }

        // Update Sidebar Active Class
        if (sidebarNavEl && newSidebarNav) {
            sidebarNavEl.innerHTML = newSidebarNav.innerHTML;
        }

        // Update Main Content Container with Premium Enter Animation
        if (mainEl && newMain) {
            mainEl.classList.remove('spa-loading');
            mainEl.classList.add('spa-content-enter');
            
            mainEl.innerHTML = newMain.innerHTML;
            mainEl.scrollTop = 0; // Reset scroll position

            // Trigger reflow & animation frames
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    mainEl.classList.remove('spa-content-enter');
                    mainEl.classList.add('spa-content-enter-active');
                    
                    setTimeout(() => {
                        mainEl.classList.remove('spa-content-enter-active');
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

        // Update and execute scripts
        if (pageScriptsEl && newPageScripts) {
            pageScriptsEl.innerHTML = newPageScripts.innerHTML;
            const scripts = pageScriptsEl.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                // Copy all attributes
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                // Copy inner JS content
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                // Execute by appending to body and immediately removing
                document.body.appendChild(newScript);
                newScript.remove();
            });
        }

        // Restore original event listeners
        document.addEventListener = originalDocAddEventListener;
        window.addEventListener = originalWinAddEventListener;

        // Resume AlpineJS mutation observing and re-initialize components
        if (window.Alpine) {
            try {
                window.Alpine.startObservingMutations();
                if (mainEl) window.Alpine.initTree(mainEl);
                if (sidebarNavEl) window.Alpine.initTree(sidebarNavEl);
            } catch (e) {
                console.error('[SPA Router] Failed to initialize Alpine tree:', e);
            }
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
        if (mainEl) mainEl.classList.remove('spa-loading');
        if (clickedLink) clickedLink.classList.remove('spa-nav-loading');
    }
}

// Intercept all same-origin link clicks
document.addEventListener('click', (e) => {
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
window.addEventListener('popstate', (e) => {
    loadPage(window.location.href, false);
});

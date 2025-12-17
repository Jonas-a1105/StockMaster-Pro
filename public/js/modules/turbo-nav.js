/**
 * =========================================================================
 * TURBO-NAV.JS - Navegación SPA (Single Page Application)
 * =========================================================================
 * Evita la recarga completa de la página en enlaces internos.
 */

// Cache simple en memoria para prefetching
const turboCache = new Map();

function initTurboNav() {
    console.log('TurboNav V2 initialized 🚀');

    // Create Top Progress Bar
    let progressBar = document.getElementById('turbo-progress');
    if (!progressBar) {
        progressBar = document.createElement('div');
        progressBar.id = 'turbo-progress';
        progressBar.style.cssText = `
            position: fixed; top: 0; left: 0; width: 0%; height: 3px;
            background: #3b82f6; z-index: 9999;
            transition: width 0.2s ease, opacity 0.3s;
            box-shadow: 0 0 10px #3b82f6;
        `;
        document.body.appendChild(progressBar);
    }

    // Main content container
    const mainContainer = document.querySelector('main');
    const pageCache = new Map();
    const PREFETCH_DELAY = 50;

    // Core Navigation Logic
    const loadPage = async (url, pushState = true) => {
        // Dispatch unload event to cleanup dropdowns y otros componentes
        window.dispatchEvent(new CustomEvent('app:page-unloaded', {
            detail: { url: url }
        }));

        // == ANIMATION OUT ==
        if (mainContainer) {
            mainContainer.style.transition = 'opacity 0.15s ease, transform 0.15s ease';
            mainContainer.style.opacity = '0.5';
            mainContainer.style.transform = 'scale(0.99)';
        }

        // Start loading UI
        progressBar.style.width = '30%';
        progressBar.style.opacity = '1';

        try {
            // Check cache or fetch
            let html;
            if (pageCache.has(url)) {
                html = pageCache.get(url);
                progressBar.style.width = '70%';
            } else {
                const res = await fetch(url);
                if (!res.ok) throw new Error('Network error');
                html = await res.text();
            }

            // Parse content
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMain = doc.querySelector('main');
            const newTitle = doc.querySelector('title');

            if (!newMain) {
                window.location.href = url;
                return;
            }

            // Update History
            if (pushState) {
                window.history.pushState({}, '', url);
            }

            if (newTitle) document.title = newTitle.textContent;

            // == SWAP CONTENT ==
            if (mainContainer) {
                mainContainer.innerHTML = newMain.innerHTML;
                mainContainer.style.opacity = '';
                mainContainer.style.transform = '';
                mainContainer.style.transition = '';

                // == ANIMATION IN ==
                mainContainer.classList.remove('animate-fade-in-up');
                void mainContainer.offsetWidth;
                mainContainer.classList.add('animate-fade-in-up');

                setTimeout(() => {
                    mainContainer.classList.remove('animate-fade-in-up');
                }, 500);
            }

            // Finish loading UI
            progressBar.style.width = '100%';
            setTimeout(() => {
                progressBar.style.opacity = '0';
                setTimeout(() => { progressBar.style.width = '0%'; }, 200);
            }, 300);

            // Re-init scripts
            executeScripts(mainContainer);

            // === RE-INIT MODULES ===
            if (window.ExchangeRate) {
                ExchangeRate.init();
                ExchangeRate.configurarManual();
            }
            if (window.Modals) Modals.init();
            if (window.Notifications) Notifications.initFlash();

            // Re-init app components
            if (typeof window.inicializarPaginaActual === 'function') {
                window.inicializarPaginaActual();
            }

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Notify app navigation finished
            window.dispatchEvent(new CustomEvent('app:page-loaded', {
                detail: { url: url }
            }));

        } catch (err) {
            console.error('TurboNav failed:', err);
            window.location.href = url;
        }
    };

    // Handle internal links
    document.addEventListener('click', async (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
            link.target === '_blank' || link.hasAttribute('download')) return;

        const url = new URL(link.href, window.location.origin);
        if (url.origin !== window.location.origin) return;

        e.preventDefault();
        loadPage(url.href, true);
    });

    // Handle Back/Forward buttons
    window.addEventListener('popstate', () => {
        loadPage(window.location.href, false);
    });

    // == PREFETCHING ==
    document.addEventListener('mouseover', (e) => {
        const link = e.target.closest('a');
        if (!link) return;
        const href = link.href;

        if (!href.includes(window.location.origin) || href.includes('#')) return;

        if (!pageCache.has(href)) {
            setTimeout(() => {
                if (link.matches(':hover')) {
                    fetch(href)
                        .then(r => r.text())
                        .then(h => pageCache.set(href, h))
                        .catch(() => { });
                }
            }, PREFETCH_DELAY);
        }
    });
}

/**
 * Execute inner scripts of new content
 */
function executeScripts(container) {
    const scripts = container.querySelectorAll('script');
    Array.from(scripts).forEach(oldScript => {
        const type = oldScript.getAttribute('type');
        if (type && type.toLowerCase() !== 'text/javascript' && type.toLowerCase() !== 'module') {
            return;
        }

        try {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.textContent = oldScript.textContent;
            document.body.appendChild(newScript);
            document.body.removeChild(newScript);

            if (oldScript.parentNode) {
                oldScript.parentNode.removeChild(oldScript);
            }
        } catch (e) {
            console.error('Error re-executing script:', e);
        }
    });
}

// Exponer globalmente
window.TurboNav = {
    init: initTurboNav
};
window.initTurboNav = initTurboNav;

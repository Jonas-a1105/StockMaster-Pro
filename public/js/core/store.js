/**
 * =========================================================================
 * STORE.JS - Centralized Reactive State Management
 * =========================================================================
 * Uses Proxy API to intercept changes and emit events automatically.
 */

const initialState = {
    tasa: 0,
    posCart: [],
    compraCart: [],
    theme: localStorage.getItem('theme') || 'light',
    currencyMode: localStorage.getItem('currencyMode') || 'mixed',
    notifications: []
};

// Internal storage
const state = { ...initialState };

/**
 * Reactive Store Proxy
 */
const Store = new Proxy(state, {
    set(target, property, value) {
        const oldValue = target[property];
        target[property] = value;

        // Emit a generic event for any change
        window.dispatchEvent(new CustomEvent('app:store-change', {
            detail: { property, value, oldValue }
        }));

        // Emit a specific event for this property
        window.dispatchEvent(new CustomEvent(`app:store:${property}`, {
            detail: { value, oldValue }
        }));

        console.log(`[Store] ${property} updated:`, value);
        return true;
    },
    get(target, property) {
        return target[property];
    }
});

// Helper to subscribe to changes easily
Store.subscribe = (property, callback) => {
    window.addEventListener(`app:store:${property}`, (e) => callback(e.detail.value, e.detail.oldValue));
};

// Global Exposure
window.Store = Store;

console.log('[Store] Reactive store initialized ✓');

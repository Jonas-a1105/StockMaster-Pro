/**
 * Service Worker para POS Offline
 * Permite que el POS funcione sin conexión a internet
 */

const CACHE_NAME = 'saas-pos-v1';
const OFFLINE_URL = 'offline.html';

// Recursos para cachear
const CACHE_ASSETS = [
    './',
    'css/style.css',
    'js/app.js',
    'offline.html',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css',
    'https://code.jquery.com/jquery-3.6.0.min.js'
];

// Instalación del Service Worker
self.addEventListener('install', (event) => {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[SW] Caching assets');
            return cache.addAll(CACHE_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activación
self.addEventListener('activate', (event) => {
    console.log('[SW] Activated');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Interceptar peticiones
self.addEventListener('fetch', (event) => {
    // Solo cachear GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Cachear respuestas exitosas
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // Sin conexión: servir desde caché
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Si es navegación, mostrar página offline
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                    return new Response('Offline', { status: 503 });
                });
            })
    );
});

// Sincronización en segundo plano
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-ventas') {
        event.waitUntil(syncPendingVentas());
    }
});

// Sincronizar ventas pendientes
async function syncPendingVentas() {
    console.log('[SW] Syncing pending sales...');

    // Leer ventas pendientes de IndexedDB
    const db = await openDB();
    const tx = db.transaction('ventas_pendientes', 'readonly');
    const store = tx.objectStore('ventas_pendientes');
    const ventas = await store.getAll();

    for (const venta of ventas) {
        try {
            const response = await fetch('index.php?controlador=venta&accion=checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(venta)
            });

            if (response.ok) {
                // Eliminar venta sincronizada
                const deleteTx = db.transaction('ventas_pendientes', 'readwrite');
                await deleteTx.objectStore('ventas_pendientes').delete(venta.id);
                console.log('[SW] Venta sincronizada:', venta.id);
            }
        } catch (error) {
            console.error('[SW] Error syncing:', error);
        }
    }
}

// Helper para abrir IndexedDB
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('SaaSPOS', 1);
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

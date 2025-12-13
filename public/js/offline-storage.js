/**
 * POS Offline - Almacenamiento local con IndexedDB
 * Permite guardar ventas localmente cuando no hay conexión
 */

class OfflineStorage {
    constructor() {
        this.dbName = 'SaaSPOS';
        this.dbVersion = 1;
        this.db = null;
    }

    /**
     * Inicializar IndexedDB
     */
    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                console.log('[OfflineStorage] Inicializado');
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Store para ventas pendientes
                if (!db.objectStoreNames.contains('ventas_pendientes')) {
                    const ventasStore = db.createObjectStore('ventas_pendientes', {
                        keyPath: 'id',
                        autoIncrement: true
                    });
                    ventasStore.createIndex('fecha', 'fecha', { unique: false });
                }

                // Store para productos (caché offline)
                if (!db.objectStoreNames.contains('productos_cache')) {
                    const productosStore = db.createObjectStore('productos_cache', {
                        keyPath: 'id'
                    });
                    productosStore.createIndex('nombre', 'nombre', { unique: false });
                    productosStore.createIndex('codigo_barras', 'codigo_barras', { unique: false });
                }

                // Store para clientes (caché offline)
                if (!db.objectStoreNames.contains('clientes_cache')) {
                    db.createObjectStore('clientes_cache', { keyPath: 'id' });
                }

                console.log('[OfflineStorage] Stores creados');
            };
        });
    }

    /**
     * Guardar venta pendiente
     */
    async guardarVentaPendiente(venta) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('ventas_pendientes', 'readwrite');
            const store = tx.objectStore('ventas_pendientes');

            venta.fecha = new Date().toISOString();
            venta.sincronizado = false;

            const request = store.add(venta);
            request.onsuccess = () => {
                console.log('[OfflineStorage] Venta guardada:', request.result);
                resolve(request.result);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Obtener ventas pendientes
     */
    async obtenerVentasPendientes() {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('ventas_pendientes', 'readonly');
            const store = tx.objectStore('ventas_pendientes');
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Eliminar venta pendiente (después de sincronizar)
     */
    async eliminarVentaPendiente(id) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('ventas_pendientes', 'readwrite');
            const store = tx.objectStore('ventas_pendientes');
            const request = store.delete(id);

            request.onsuccess = () => resolve(true);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Cachear productos para uso offline
     */
    async cachearProductos(productos) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('productos_cache', 'readwrite');
            const store = tx.objectStore('productos_cache');

            // Limpiar cache existente
            store.clear();

            // Agregar productos
            productos.forEach(p => store.put(p));

            tx.oncomplete = () => {
                console.log('[OfflineStorage] Productos cacheados:', productos.length);
                resolve(true);
            };
            tx.onerror = () => reject(tx.error);
        });
    }

    /**
     * Buscar productos en caché local
     */
    async buscarProductos(termino) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('productos_cache', 'readonly');
            const store = tx.objectStore('productos_cache');
            const request = store.getAll();

            request.onsuccess = () => {
                const productos = request.result;
                const terminoLower = termino.toLowerCase();

                const resultados = productos.filter(p =>
                    p.nombre.toLowerCase().includes(terminoLower) ||
                    (p.codigo_barras && p.codigo_barras.includes(termino))
                );

                resolve(resultados.slice(0, 10));
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Contar ventas pendientes de sincronizar
     */
    async contarPendientes() {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('ventas_pendientes', 'readonly');
            const store = tx.objectStore('ventas_pendientes');
            const request = store.count();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }
}

// Instancia global
window.offlineStorage = new OfflineStorage();

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await window.offlineStorage.init();

        // Mostrar indicador de ventas pendientes
        const pendientes = await window.offlineStorage.contarPendientes();
        if (pendientes > 0) {
            console.log('[OfflineStorage] Ventas pendientes:', pendientes);
            // Mostrar badge en algún lugar de la UI
        }
    } catch (error) {
        console.error('[OfflineStorage] Error inicializando:', error);
    }
});

// Sincronizar cuando vuelve la conexión
window.addEventListener('online', async () => {
    console.log('[OfflineStorage] Conexión restaurada, sincronizando...');

    if ('serviceWorker' in navigator && 'SyncManager' in window) {
        const registration = await navigator.serviceWorker.ready;
        await registration.sync.register('sync-ventas');
    }
});

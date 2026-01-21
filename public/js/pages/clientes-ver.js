/**
 * =========================================================================
 * CLIENTES-VER.JS - Módulo de Detalle de Cliente
 * =========================================================================
 */

console.log('[ClientesVer] Módulo cargando...');

// =========================================================================
// LÓGICA DE TABS
// =========================================================================
function showTab(tabId) {
    // Hide all
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('text-indigo-600', 'border-indigo-600', 'active', 'bg-indigo-50/50', 'dark:text-indigo-400', 'dark:border-indigo-400');
        el.classList.add('text-slate-500', 'border-transparent');
    });

    // Show Target
    document.getElementById(tabId)?.classList.remove('hidden');

    // Activate Button
    const btn = document.querySelector(`button[data-tab="${tabId}"]`);
    if (btn) {
        btn.classList.remove('text-slate-500', 'border-transparent');
        btn.classList.add('text-indigo-600', 'border-indigo-600', 'active', 'bg-indigo-50/50', 'dark:text-indigo-400', 'dark:border-indigo-400');
    }

    if (tabId === 'historial') {
        cargarYCalcularBs();
    }
}

// =========================================================================
// CONVERSIÓN DE TASA DE CAMBIO
// =========================================================================
async function cargarYCalcularBs() {
    try {
        let tasa = window.tasaCambioBS || 0;
        if (tasa <= 0) {
            const data = await Endpoints.obtenerTasa();
            tasa = data.tasa || 0;
        }

        if (tasa > 0) {
            document.querySelectorAll('.precio-bs').forEach(span => {
                const usd = parseFloat(span.dataset.usd) || 0;
                span.textContent = (usd * tasa).toFixed(2);
            });
        }
    } catch (e) {
        console.error('Error calculando BCV', e);
    }
}

// =========================================================================
// DESACTIVAR CLIENTE
// =========================================================================
function desactivarCliente(id) {
    // La confirmación ya la maneja el modal. Procedemos directo.

    // Opcional: Mostrar loading en el botón si se pasara el evento, pero por ahora simple alert/redirect

    Endpoints.desactivarCliente(id)
        .then(data => {
            if (data.success) {
                // Usamos el sistema de notificaciones si está disponible, o alert fallback
                if (window.Notifications) {
                    window.Notifications.show('Cliente eliminado correctamente', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php?controlador=cliente&accion=index';
                    }, 1000);
                } else {
                    alert('Cliente eliminado correctamente.');
                    window.location.href = 'index.php?controlador=cliente&accion=index';
                }
            } else {
                if (window.Notifications) {
                    window.Notifications.show('Error: ' + data.message, 'error');
                } else {
                    alert('Error: ' + data.message);
                }
            }
        })
        .catch(err => console.error(err));
}

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.showTab = showTab;
window.cargarYCalcularBs = cargarYCalcularBs;
window.desactivarCliente = desactivarCliente;

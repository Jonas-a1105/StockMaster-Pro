/**
 * =========================================================================
 * CONFIG.JS - Módulo de Configuración
 * =========================================================================
 */

console.log('[Config] Módulo cargando...');

// =========================================================================
// PREVIEW LOGO
// =========================================================================
function previewLogo(input) {
    const fileName = document.getElementById('file-name');
    if (!fileName) return;

    if (input.files && input.files[0]) {
        fileName.textContent = input.files[0].name;
    } else {
        fileName.textContent = 'Ningún archivo seleccionado';
    }
}

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.previewLogo = previewLogo;

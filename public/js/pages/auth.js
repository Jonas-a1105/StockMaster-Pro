/**
 * =========================================================================
 * AUTH.JS - Módulo de Autenticación (Login/Registro)
 * =========================================================================
 */

console.log('[Auth] Módulo cargando...');

// =========================================================================
// LOGIN FORM HANDLER
// =========================================================================
function initLoginForm() {
    const form = document.getElementById('login-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const btn = document.getElementById('btn-submit');
        const text = document.getElementById('btn-text');
        const icon = document.getElementById('btn-icon');
        const loader = document.getElementById('btn-loader');

        // Show Button Loading State
        if (text) text.textContent = 'Verificando...';
        icon?.classList.add('hidden');
        loader?.classList.remove('hidden');
        if (btn) btn.disabled = true;
    });

    console.log('[Auth] Login form inicializado ✓');
}

// =========================================================================
// REGISTER FORM HANDLER
// =========================================================================
function initRegisterForm() {
    const form = document.getElementById('register-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const pass = document.getElementById('password')?.value;
        const confirm = document.getElementById('password_confirm')?.value;

        if (pass !== confirm) {
            e.preventDefault();
            if (typeof showToast === 'function') {
                showToast('Las contraseñas no coinciden', 'error');
            } else {
                alert('Las contraseñas no coinciden');
            }
            return;
        }

        // Loader UI
        const btn = document.getElementById('btn-submit');
        const text = document.getElementById('btn-text');
        const icon = document.getElementById('btn-icon');
        const loader = document.getElementById('btn-loader');
        const loadingScreen = document.getElementById('loading-screen');

        if (text) text.textContent = 'Creando cuenta...';
        icon?.classList.add('hidden');
        loader?.classList.remove('hidden');
        if (btn) btn.disabled = true;
        loadingScreen?.classList.remove('hidden');
    });

    console.log('[Auth] Register form inicializado ✓');
}

// =========================================================================
// INICIALIZACIÓN
// =========================================================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initLoginForm();
        initRegisterForm();
    });
} else {
    initLoginForm();
    initRegisterForm();
}

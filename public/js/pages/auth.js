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
// RECOVERY MODAL LOGIC
// =========================================================================

// Generador de Challenge Aleatorio (4Chars-4Chars)
function generateChallenge() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No I, O, 0, 1 for clarity
    let result = '';
    for (let i = 0; i < 8; i++) {
        if (i === 4) result += '-';
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

function openRecoveryModal(e) {
    if (e) e.preventDefault();
    const challenge = generateChallenge();
    const challengeEl = document.getElementById('challenge-code');
    if (challengeEl) challengeEl.textContent = challenge;

    // Pre-fill username from main form if exists
    const mainUser = document.querySelector('input[name="username"]')?.value;
    const unlockUserEl = document.getElementById('unlock-username');
    if (mainUser && unlockUserEl) unlockUserEl.value = mainUser;

    document.getElementById('modal-recovery')?.classList.remove('hidden');
    const responseEl = document.getElementById('unlock-response');
    if (responseEl) responseEl.value = '';
    document.getElementById('unlock-msg')?.classList.add('hidden');
}

function closeRecoveryModal() {
    document.getElementById('modal-recovery')?.classList.add('hidden');
}

async function realizarDesbloqueo(e) {
    if (e) e.preventDefault();

    const btn = e.target.querySelector('button[type="submit"]');
    if (!btn) return;

    const originalText = btn.innerText;
    btn.innerText = 'Verificando...';
    btn.disabled = true;

    const challenge = document.getElementById('challenge-code')?.textContent;
    const responseCode = document.getElementById('unlock-response')?.value.trim().toUpperCase();
    const username = document.getElementById('unlock-username')?.value.trim();

    try {
        const data = await Endpoints.verificarDesbloqueo({
            challenge: challenge,
            response: responseCode,
            username: username,
            csrf_token: window.csrfToken
        });
        const msgBox = document.getElementById('unlock-msg');

        if (msgBox) {
            msgBox.classList.remove('hidden', 'bg-red-50', 'text-red-600', 'bg-emerald-50', 'text-emerald-600');

            if (data.success) {
                msgBox.classList.add('bg-emerald-50', 'text-emerald-600');
                msgBox.textContent = data.message;
                // Success!
                setTimeout(() => {
                    if (window.Notifications) {
                        window.Notifications.show(data.message, 'success');
                    } else {
                        alert(data.message);
                    }
                    closeRecoveryModal();
                }, 1500);
            } else {
                msgBox.classList.add('bg-red-50', 'text-red-600');
                msgBox.textContent = data.message;
            }
        }

    } catch (err) {
        console.error(err);
        if (window.Notifications) {
            window.Notifications.show('Error de conexión', 'error');
        } else {
            alert('Error de conexión');
        }
    } finally {
        btn.innerText = originalText;
        btn.disabled = false;
    }
}

// Exponer globalmente
window.openRecoveryModal = openRecoveryModal;
window.closeRecoveryModal = closeRecoveryModal;
window.realizarDesbloqueo = realizarDesbloqueo;

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

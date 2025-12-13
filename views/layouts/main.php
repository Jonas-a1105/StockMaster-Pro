<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StockMaster Pro - Sistema de Gestión de Inventario">
    <title>StockMaster Pro</title>
    
    <link rel="shortcut icon" href="<?= BASE_URL ?>img/StockMasterPro.ico" type="image/x-icon">
    
    <!-- Tailwind CSS (Local Build) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/main.css?v=<?= time() ?>">
    
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        /* Scrollbar personalizado */
        html { scrollbar-gutter: stable; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Animaciones suaves */
        .fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-up { animation: slideUp 0.3s ease-out; }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Turbo Nav Transition */
        .animate-fade-in-up {
            animation: fadeInUp 200ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        /* Truncar texto con ellipsis */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Input focus ring */
        .input-focus {
            @apply focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500;
        }
        
        /* Tabla zebra */
        .table-zebra tbody tr:nth-child(even) {
            @apply bg-slate-50/50;
        }
        
        /* Glass effect para overlays */
        .glass {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        
        /* Dark mode transitions */
        * { transition: background-color 0.2s, border-color 0.2s, color 0.2s; }

        /* FORCE RESPONSIVENESS (Fix for missing Tailwind classes) */
        @media (max-width: 640px) {
            main {
                padding-left: 0.5rem !important; /* px-2 */
                padding-right: 0.5rem !important;
                padding-top: 1rem !important;
            }
            .nav-custom-container {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            /* Main Box */
            main > div {
                border-radius: 1rem !important; /* rounded-2xl */
                min-height: calc(100vh - 140px) !important;
            }
            /* Content Padding */
            main > div > div {
                padding: 1rem !important; /* p-4 */
            }
        }

        /* FORCE NAVBAR RESPONSIVENESS (Break at 1024px/lg) */
        @media (max-width: 1024px) {
            /* Hide Desktop Menu */
            .hidden.lg\:flex, 
            nav .lg\:flex {
                display: none !important;
            }
            /* Show Mobile Toggle */
            .lg\:hidden {
                display: flex !important; /* Assuming flex layout for buttons */
            }
            /* Mobile Menu Container */
            #mobile-menu.lg\:hidden {
                 /* This is complex because it toggles with 'hidden' class via JS. 
                    We just need to ensure it's ALLOWED to show if 'hidden' is removed.
                    The 'lg:hidden' class on the container means "Hide on LG, show on small". 
                    So on <1024, it should be display: block (default) unless 'hidden' is present.
                 */
                 display: block; /* Default state for opened menu */
            }
            #mobile-menu.hidden {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-[#eef2f6] dark:bg-slate-900 min-h-screen font-sans antialiased">
    <?php
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        
        // Incluir el helper de iconos
        require_once __DIR__ . '/../../src/Helpers/Icons.php';
    ?>
    
    <!-- NAVBAR (Full Width) -->
    <?php require __DIR__ . '/../partials/navbar-enterprise.php'; ?>
    
    <!-- CONTENEDOR PRINCIPAL (Boxed Layout) -->
    <main class="max-w-[1440px] mx-auto px-2 sm:px-4 lg:px-8 py-3 sm:py-6">
        <!-- Caja Principal -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl sm:rounded-[32px] border border-slate-200 dark:border-slate-700 shadow-soft min-h-[calc(100vh-140px)] sm:min-h-[calc(100vh-180px)] flex flex-col overflow-hidden">
            
            <!-- Contenido de la Vista -->
            <div class="flex-1 p-4 sm:p-6 lg:p-8">
                <?php
                    if (isset($vistaContenido) && file_exists($vistaContenido)) {
                        require $vistaContenido;
                    } else {
                        echo '<div class="text-center py-20">
                            <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-slate-800 dark:text-white">Vista no encontrada</h2>
                            <p class="text-slate-500 mt-2">La página que buscas no existe.</p>
                        </div>';
                    }
                ?>
            </div>
            
            <!-- Footer Sticky -->
            <?php require __DIR__ . '/../partials/footer-enterprise.php'; ?>
            
        </div>
    </main>
    
    <!-- MODALES GLOBALES -->
    
    <!-- Modal Logout -->
    <div id="modal-logout" class="hidden fixed inset-0 z-[100]">
        <div class="fixed inset-0 bg-slate-900/50 glass" onclick="closeModal('modal-logout')"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-6 relative fade-in">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <span class="text-4xl">👋</span>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-800 dark:text-white mb-2">¿Ya te vas?</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-6">Tu sesión se cerrará de forma segura.</p>
                    <div class="flex gap-3">
                        <button onclick="closeModal('modal-logout')" class="flex-1 px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Cancelar
                        </button>
                        <a href="index.php?controlador=login&accion=logout" class="flex-1 px-4 py-2.5 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-colors text-center">
                            Sí, salir
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Confirmar Eliminar -->
    <div id="modal-confirmar-eliminar" class="hidden fixed inset-0 z-[100]">
        <div class="fixed inset-0 bg-slate-900/50 glass" onclick="closeModal('modal-confirmar-eliminar')"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-6 relative fade-in">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <?= \App\Helpers\Icons::get('trash', 'w-8 h-8 text-red-500') ?>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-800 dark:text-white mb-2">¿Eliminar elemento?</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-6">Esta acción no se puede deshacer.</p>
                    <div class="flex gap-3">
                        <button onclick="closeModal('modal-confirmar-eliminar')" id="cancelar-modal-eliminar" class="flex-1 px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Cancelar
                        </button>
                        <button id="btn-confirmar-eliminar" class="flex-1 px-4 py-2.5 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-colors">
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-[200] space-y-2"></div>
    
    <!-- SCRIPTS -->
    <!-- SCRIPTS -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>
    
    <!-- Enterprise UI Scripts -->
    <script>
        // === DROPDOWNS ===
        function setupDropdown(btnId, dropdownId, containerId) {
            const btn = document.getElementById(btnId);
            const dropdown = document.getElementById(dropdownId);
            const container = document.getElementById(containerId);
            
            if (!btn || !dropdown) return;
            
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
            });
            
            document.addEventListener('click', (e) => {
                if (container && !container.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }
        
        // === MODALES ===
        // La lógica de modales ahora se maneja centralizadamente en js/modules/modals.js
        // para garantizar el comportamiento de "Portal" y z-index correcto.

        
        // === TOAST NOTIFICATIONS ===
        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toast-container');
            const colors = {
                success: 'bg-emerald-500',
                error: 'bg-red-500',
                warning: 'bg-amber-500',
                info: 'bg-blue-500'
            };
            
            const toast = document.createElement('div');
            toast.className = `${colors[type]} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 slide-up`;
            toast.innerHTML = `
                <span class="flex-1 text-sm font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
        
        // Exponer globalmente para compatibilidad
        window.mostrarNotificacion = function(msg, type) {
            showToast(msg, type === 'success' ? 'success' : (type === 'error' ? 'error' : 'warning'));
        };
        
        // === TEMA ===
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            
            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            
            updateThemeIcons();
        }
        
        function updateThemeIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            document.getElementById('theme-icon-light')?.classList.toggle('hidden', isDark);
            document.getElementById('theme-icon-dark')?.classList.toggle('hidden', !isDark);
        }
        
        function initTheme() {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
            
            updateThemeIcons();
        }
        
        // === MENÚ MÓVIL ===
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu?.classList.toggle('hidden');
        }
        
        // === INIT ===
        document.addEventListener('DOMContentLoaded', () => {
            // Tema
            initTheme();
            document.getElementById('btn-theme-toggle')?.addEventListener('click', toggleTheme);
            
            // Dropdowns
            setupDropdown('btn-notificaciones', 'notif-dropdown', 'notif-container');
            setupDropdown('btn-user-menu', 'user-dropdown', 'user-menu-container');
            
            // Menú móvil
            document.getElementById('btn-mobile-menu')?.addEventListener('click', toggleMobileMenu);
            
            // Logout
            document.getElementById('btn-logout-trigger')?.addEventListener('click', () => openModal('modal-logout'));
            
            // ESC para cerrar modales
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    document.querySelectorAll('[id^="modal-"]').forEach(m => {
                        if (!m.classList.contains('hidden')) closeModal(m.id);
                    });
                }
            });
        });
    </script>
</body>
</html>
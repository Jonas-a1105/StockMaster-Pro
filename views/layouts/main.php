<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StockMaster Pro - Sistema de Gestión de Inventario">
    <title><?php echo $titulo ?? 'StockMaster Pro'; ?></title>
    
    <link rel="shortcut icon" href="<?= BASE_URL ?>img/StockMasterPro.ico" type="image/x-icon">
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/variables.css">
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
        
        /* Animaciones suaves - Aceleradas para igualar TurboNav */
        .fade-in { animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.98) translateY(-5px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        
        .slide-up { animation: slideUp var(--transition-slow); }
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
        .input-focus:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
            border-color: #10b981;
        }
        
        /* Tabla zebra */
        .table-zebra tbody tr:nth-child(even) {
            background-color: rgba(248, 250, 252, 0.5);
        }
        
        /* Glass effect para overlays */
        .glass {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        
        /* Dark mode transitions */
        * { transition: background-color var(--transition-fast), border-color var(--transition-fast), color var(--transition-fast); }

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
    
    <!-- Skip to Content Link (Accesibilidad) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-emerald-600 focus:text-white focus:rounded-lg focus:font-bold focus:shadow-lg">
        Saltar al contenido principal
    </a>
    
    <!-- NAVBAR (Full Width) -->
    <?php require __DIR__ . '/../partials/navbar-enterprise.php'; ?>
    
    <!-- CONTENEDOR PRINCIPAL (Boxed Layout) -->
    <main id="main-content" class="max-w-[1440px] mx-auto px-2 sm:px-4 lg:px-8 py-3 sm:py-6" role="main">

        <!-- Caja Principal -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl sm:rounded-[32px] border border-slate-200 dark:border-slate-700 shadow-soft min-h-[calc(100vh-140px)] sm:min-h-[calc(100vh-180px)] flex flex-col overflow-visible">
            
            <!-- Contenido de la Vista -->
            <div class="flex-1 p-4 sm:p-6 lg:p-8">
                <script>
                    // Global CSRF Token for AJAX requests
                    window.csrfToken = '<?php echo \App\Core\Session::csrfToken(); ?>';
                </script>
                <?php echo $content ?? ''; ?>
            </div>
            
            <!-- Footer Sticky -->
            <?php require __DIR__ . '/../partials/footer-enterprise.php'; ?>
            
        </div>
    </main>
    
    <!-- MODALES GLOBALES -->
    
    <!-- Modal Logout -->
    <div id="modal-logout" class="hidden fixed inset-0 z-[100]">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
        <!-- Wrapper -->
        <div class="fixed inset-0 flex items-center justify-center p-4" onclick="if(event.target === this) closeModal('modal-logout')">
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
                        <form action="index.php?controlador=login&accion=logout" method="POST" class="flex-1">
                            <?= \App\Helpers\Security::csrfField() ?>
                            <button type="submit" class="w-full px-4 py-2.5 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-colors text-center">
                                Sí, salir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Confirmar Eliminar -->
    <div id="modal-confirmar-eliminar" class="hidden fixed inset-0 z-[100]">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
        <!-- Wrapper -->
        <div class="fixed inset-0 flex items-center justify-center p-4" onclick="if(event.target === this) closeModal('modal-confirmar-eliminar')">
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
    
    <!-- Toast Notification Container (Top Right) -->
    <div id="toast-container-main" class="fixed top-20 right-4 z-[200] space-y-3 w-full max-w-sm pointer-events-none"></div>

    
    <!-- Flash Messages (Bridge to JS) -->
    <?php 
        // Verificar si hay mensajes flash en la sesión y pasarlos al JS
        if (\App\Core\Session::hasFlash()) {
            $flashMsg = '';
            $flashType = 'success';
            
            if ($msg = \App\Core\Session::getFlash('success')) {
                $flashMsg = $msg;
                $flashType = 'success';
            } elseif ($msg = \App\Core\Session::getFlash('error')) {
                $flashMsg = $msg;
                $flashType = 'error';
            } elseif ($msg = \App\Core\Session::getFlash('warning')) {
                $flashMsg = $msg;
                $flashType = 'warning';
            }
            
            if ($flashMsg) {
                echo "<div id='flash-data' data-message='" . htmlspecialchars($flashMsg, ENT_QUOTES) . "' data-type='" . $flashType . "' class='hidden'></div>";
            }
        }
    ?>
    
    <!-- SCRIPTS -->
    <?php require __DIR__ . '/../partials/scripts.php'; ?>
    
    <!-- Enterprise UI Scripts -->
    <script src="<?= BASE_URL ?>js/core/store.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>js/core/core.js?v=<?= time() ?>"></script>
    
</body>
</html>
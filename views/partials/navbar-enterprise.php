<?php
/**
 * NAVBAR ENTERPRISE - Barra de navegación nivel Enterprise
 */
use App\Helpers\Icons;

$plan = $_SESSION['user_plan'] ?? 'free';
$rol = $_SESSION['user_rol'] ?? 'usuario';
$userName = $_SESSION['user_name'] ?? 'Usuario';

// Obtener notificaciones
$notifNoLeidas = 0;
$notificaciones = [];
if ($plan === 'premium' && isset($_SESSION['user_id'])) {
    $notificacionModel = new \App\Models\NotificacionModel();
    $notifNoLeidas = $notificacionModel->contarNoLeidas($_SESSION['user_id']);
    $notificaciones = $notificacionModel->obtenerNoLeidas($_SESSION['user_id'], 5);
}

// Navegación según plan
$homeLink = ($plan === 'free') ? 'index.php?controlador=free&accion=index' : 'index.php?controlador=dashboard&accion=index';

$navItems = [
    ['url' => $homeLink, 'icon' => 'dashboard', 'label' => 'Inicio'],
];

if ($plan === 'premium') {
    $navItems = array_merge($navItems, [
        ['url' => 'index.php?controlador=producto&accion=index', 'icon' => 'inventory', 'label' => 'Inventario'],
        ['url' => 'index.php?controlador=venta&accion=index', 'icon' => 'pos', 'label' => 'Vender'],
        ['url' => 'index.php?controlador=compra&accion=index', 'icon' => 'purchases', 'label' => 'Compras'],
        ['url' => 'index.php?controlador=movimiento&accion=index', 'icon' => 'movements', 'label' => 'Movimientos'],
        ['url' => 'index.php?controlador=proveedor&accion=index', 'icon' => 'suppliers', 'label' => 'Proveedores'],
        ['url' => 'index.php?controlador=cliente&accion=index', 'icon' => 'clients', 'label' => 'Clientes'],
        ['url' => 'index.php?controlador=reporte&accion=index', 'icon' => 'reports', 'label' => 'Reportes'],
    ]);
}
?>

<nav class="bg-white border-b border-slate-200 sticky top-0 z-50 block w-full !p-0">
    <div class="w-full px-2 sm:px-4 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Izquierda: Logo + Navegación -->
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <!-- Logo -->
                <div class="flex items-center gap-3 group cursor-pointer" onclick="window.location.href='<?= $homeLink ?>'">
                    <div class="relative w-12 h-12 bg-emerald-50 border border-emerald-200 rounded-xl p-0.5 shadow-sm transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:shadow-md overflow-hidden">
                        <svg class="w-full h-full" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="grad_stock" x1="0" y1="64" x2="0" y2="0" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#D1FAE5"/>
                                    <stop offset="1" stop-color="#34D399"/>
                                </linearGradient>
                                <linearGradient id="grad_growth" x1="10" y1="50" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#10B981"/>
                                    <stop offset="1" stop-color="#047857"/>
                                </linearGradient>
                                <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                                    <feDropShadow dx="0" dy="3" stdDeviation="2" flood-color="#064E3B" flood-opacity="0.2"/>
                                </filter>
                            </defs>
                            <!-- Inventario -->
                            <rect x="12" y="38" width="10" height="7" rx="1" fill="url(#grad_stock)" opacity="0.5"/>
                            <rect x="12" y="46" width="10" height="7" rx="1" fill="url(#grad_stock)" opacity="0.6"/>
                            <rect x="26" y="30" width="10" height="7" rx="1" fill="url(#grad_stock)" opacity="0.7"/>
                            <rect x="26" y="38" width="10" height="7" rx="1" fill="url(#grad_stock)" opacity="0.8"/>
                            <rect x="26" y="46" width="10" height="7" rx="1" fill="url(#grad_stock)" opacity="0.9"/>
                            <rect x="40" y="22" width="10" height="7" rx="1" fill="url(#grad_stock)" />
                            <rect x="40" y="30" width="10" height="7" rx="1" fill="url(#grad_stock)" />
                            <rect x="40" y="38" width="10" height="7" rx="1" fill="url(#grad_stock)" />
                            <rect x="40" y="46" width="10" height="7" rx="1" fill="url(#grad_stock)" />
                            <!-- Dinero -->
                            <circle cx="10" cy="50" r="5" fill="#10B981" stroke="white" stroke-width="1.5" filter="url(#shadow)" />
                            <path d="M10 47V53M7 50H13" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            <!-- Flecha Hoja -->
                            <path d="M10 50 C 18 50, 24 35, 34 38 C 42 40, 46 25, 54 12" stroke="url(#grad_growth)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none" filter="url(#shadow)"/>
                            <path d="M54 12 C 54 12, 44 14, 42 22 C 42 22, 58 24, 60 14 C 60 14, 58 8, 54 12 Z" fill="#10B981" stroke="white" stroke-width="1" filter="url(#shadow)"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-slate-800">StockMaster <span class="text-emerald-500">Pro</span></span>
                </div>
                
                <!-- Navegación Principal -->
                <div class="hidden lg:flex items-center gap-1">
                    <?php 
                    $currentCtrl = $_GET['controlador'] ?? ($plan === 'free' ? 'free' : 'dashboard');
                    
                    foreach ($navItems as $item): 
                        // Simple logic to determine active state: check if URL contains current controller
                        $isActive = false;
                        parse_str(parse_url($item['url'], PHP_URL_QUERY), $queryParams);
                        if (isset($queryParams['controlador']) && $queryParams['controlador'] === $currentCtrl) {
                            $isActive = true;
                        } else if ($currentCtrl === 'dashboard' && strpos($item['url'], 'controlador=dashboard') !== false) {
                            $isActive = true; 
                        }
                    ?>
                        <a href="<?= $item['url'] ?>" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 border-2
                                  <?= $isActive 
                                      ? 'bg-emerald-50 text-emerald-700 border-emerald-500 shadow-sm hover:bg-emerald-100' // Active State
                                      : 'text-slate-600 border-transparent hover:text-emerald-600 hover:bg-slate-50' // Inactive State
                                  ?>">
                            <?= Icons::get($item['icon'], 'w-4 h-4') ?>
                            <span><?= $item['label'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Perfil y Acciones -->
            <div class="flex items-center gap-4">
                <?php if ($plan === 'premium'): ?>                    
                    <!-- Tasa de Cambio -->
                    <!-- Tasa de Cambio (Input Rápido) -->
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-slate-100 border border-transparent hover:border-slate-200 focus-within:bg-white focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 rounded-lg transition-all">
                        <span class="text-xs text-slate-500 font-medium whitespace-nowrap">USD:</span>
                        <input type="number" id="nav-tasa-input" step="0.01" 
                               class="w-28 bg-transparent border-none p-0 text-sm font-bold text-slate-700 focus:ring-0 text-right" 
                               placeholder="0.00">
                        <button id="nav-btn-update" 
                                class="ml-1 p-1 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-md transition-colors"
                                title="Actualizar Tasa Manual">
                            <?= Icons::get('check', 'w-4 h-4') ?>
                        </button>
                    </div>
                    
                    <!-- Notificaciones -->
                    <div class="relative" id="notif-container">
                        <button id="btn-notificaciones" class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                            <?= Icons::get('bell', 'w-5 h-5') ?>
                            <?php if ($notifNoLeidas > 0): ?>
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                                    <?= $notifNoLeidas > 9 ? '9+' : $notifNoLeidas ?>
                                </span>
                            <?php endif; ?>
                        </button>
                        
                        <!-- Dropdown Notificaciones -->
                        <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-50">
                            <div class="px-4 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Notificaciones</span>
                                    <?php if ($notifNoLeidas > 0): ?>
                                        <button id="marcar-todas-leidas" class="text-xs text-emerald-100 hover:text-white">
                                            Marcar todas
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <?php if (empty($notificaciones)): ?>
                                    <div class="py-8 text-center text-slate-400">
                                        <?= Icons::get('check-circle', 'w-12 h-12 mx-auto mb-2 text-emerald-200') ?>
                                        <p class="text-sm">No hay notificaciones</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($notificaciones as $notif): ?>
                                        <a href="<?= $notif['link'] ?? '#' ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0">
                                            <div class="w-8 h-8 rounded-full bg-<?= $notif['prioridad'] === 'critica' ? 'red' : ($notif['prioridad'] === 'alta' ? 'amber' : 'blue') ?>-100 flex items-center justify-center flex-shrink-0">
                                                <?= Icons::get($notif['icono'] ?? 'bell', 'w-4 h-4 text-' . ($notif['prioridad'] === 'critica' ? 'red' : ($notif['prioridad'] === 'alta' ? 'amber' : 'blue')) . '-600') ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-slate-800 truncate"><?= htmlspecialchars($notif['titulo']) ?></p>
                                                <p class="text-xs text-slate-500 line-clamp-2"><?= htmlspecialchars($notif['mensaje']) ?></p>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="index.php?controlador=premium&accion=index" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-lg text-sm font-medium shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all">
                        <?= Icons::get('crown', 'w-4 h-4') ?>
                        <span>Upgrade</span>
                    </a>
                <?php endif; ?>
                
                <!-- Toggle Tema -->
                <button id="btn-theme-toggle" class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                    <span id="theme-icon-light"><?= Icons::get('moon', 'w-5 h-5') ?></span>
                    <span id="theme-icon-dark" class="hidden"><?= Icons::get('sun', 'w-5 h-5') ?></span>
                </button>
                
                <!-- Usuario / Perfil -->
                <div class="relative" id="user-menu-container">
                    <button id="btn-user-menu" class="relative group cursor-pointer focus:outline-none flex items-center gap-2">
                        <!-- Contenedor del avatar con lógica condicional -->
                        <!-- Contenedor del avatar con lógica condicional -->
                        <div class="relative w-10 h-10">
                            <?php if (isset($plan) && $plan === 'premium'): ?>
                                <!-- 1. AVATAR PREMIUM (Borde Dorado + Glow) -->
                                <!-- Usamos estilos inline para garantizar el color dorado si Tailwind no tiene la clase amber compilada -->
                                <div class="w-full h-full rounded-full flex items-center justify-center text-xs font-bold text-white shadow-md relative overflow-hidden transition-transform group-hover:scale-105"
                                     style="background-color: #0f172a; border: 2px solid #fbbf24; box-shadow: 0 0 0 2px #fef3c7;">
                                    <span class="z-10 relative"><?= strtoupper(substr($userName, 0, 2)) ?></span>
                                    <!-- Brillo interno diagonal -->
                                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent"></div>
                                </div>

                                <!-- 2. CORONA (Top-Left, Rotated, Bouncing) -->
                                <!-- Posición absoluta forzada con estilos para evitar fallos de clases JIT -->
                                <div class="absolute z-50 filter drop-shadow-sm pointer-events-none" 
                                     style="top: -12px; left: -8px; transform: rotate(-12deg); animation: bounce 3s infinite; color: #fbbf24;">
                                    <?= Icons::get('crown', 'w-5 h-5 fill-current') ?>
                                </div>
                            <?php else: ?>
                                <!-- AVATAR ESTÁNDAR (Free) -->
                                <div class="w-full h-full rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center text-xs font-bold text-slate-600 shadow-sm relative overflow-hidden transition-transform group-hover:scale-105">
                                    <span class="z-10 relative"><?= strtoupper(substr($userName, 0, 2)) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-slate-700"><?= htmlspecialchars($userName) ?></span>
                        <?= Icons::get('chevron-down', 'w-4 h-4 text-slate-400') ?>
                    </button>
                    
                    <!-- Dropdown Usuario -->
                    <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="text-sm font-medium text-slate-800"><?= htmlspecialchars($userName) ?></p>
                            <p class="text-xs text-slate-500"><?= ucfirst($plan) ?> • <?= ucfirst($rol) ?></p>
                        </div>
                        <div class="py-1">
                            <a href="index.php?controlador=perfil&accion=index" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <?= Icons::get('user', 'w-4 h-4 text-slate-400') ?>
                                Mi Perfil
                            </a>
                            <a href="index.php?controlador=acerca&accion=index" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <?= Icons::get('info', 'w-4 h-4 text-slate-400') ?>
                                Acerca de
                            </a>
                            <a href="index.php?controlador=config&accion=index" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <?= Icons::get('settings', 'w-4 h-4 text-slate-400') ?>
                                Configuración
                            </a>
                            <?php if ($rol === 'admin'): ?>
                            <a href="index.php?controlador=admin&accion=index" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <?= Icons::get('team', 'w-4 h-4 text-slate-400') ?>
                                Administración
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="border-t border-slate-100 py-1">
                            <button id="btn-logout-trigger" class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <?= Icons::get('logout', 'w-4 h-4') ?>
                                Cerrar Sesión
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Menú Móvil -->
                <button id="btn-mobile-menu" class="lg:hidden p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg">
                    <?= Icons::get('menu', 'w-5 h-5') ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Menú Móvil Expandido -->
    <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-200 bg-white">
        <div class="px-4 py-3 space-y-1">
            <?php foreach ($navItems as $item): ?>
                <a href="<?= $item['url'] ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-700 hover:bg-slate-100">
                    <?= Icons::get($item['icon'], 'w-5 h-5 text-slate-400') ?>
                    <span class="font-medium"><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const updateNavbarActiveState = (url) => {
        const currentUrl = new URL(url || window.location.href);
        const params = new URLSearchParams(currentUrl.search);
        const currentCtrl = params.get('controlador') || 'dashboard'; // Default fallback

        const links = document.querySelectorAll('nav a[href], .hidden.lg\\:flex a[href]');
        
        links.forEach(link => {
            const linkUrl = new URL(link.href, window.location.origin);
            const linkParams = new URLSearchParams(linkUrl.search);
            const linkCtrl = linkParams.get('controlador');
            
            let isActive = false;

            // Strict check for controller match
            if (linkCtrl === currentCtrl) {
                isActive = true;
            } 
            // Dashboard special case (root or explicit)
            else if (currentCtrl === 'dashboard' && (linkCtrl === 'dashboard' || !linkCtrl)) {
                 // Check if link effectively points to dashboard
                 if(link.href.includes('controlador=dashboard') || link.href.endsWith('/')) isActive = true;
            }

            // Apply Classes
            if (isActive) {
                link.classList.remove('text-slate-600', 'border-transparent', 'hover:text-emerald-600', 'hover:bg-slate-50');
                link.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-500', 'shadow-sm', 'hover:bg-emerald-100');
            } else {
                link.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-500', 'shadow-sm', 'hover:bg-emerald-100');
                link.classList.add('text-slate-600', 'border-transparent', 'hover:text-emerald-600', 'hover:bg-slate-50');
            }
        });
    };

    // Listen to custom Turbo event
    window.addEventListener('app:page-loaded', (e) => {
        if (e.detail && e.detail.url) {
            updateNavbarActiveState(e.detail.url);
        }
    });
    
    // Initial check
    updateNavbarActiveState();
});
</script>

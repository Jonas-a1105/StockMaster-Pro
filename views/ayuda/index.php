<?php use App\Helpers\Icons; ?>

<!-- Contenedor Flex sin padding extra (ya lo da el layout) -->
<div class="flex flex-col lg:flex-row gap-8 items-start h-full">
    
    <!-- Sidebar Navigation (Hidden on mobile, uses custom sticky) -->
    <!-- Ajustamos top-24 porque hay un Navbar sticky de h-16 + padding -->
    <aside class="hidden lg:block w-64 shrink-0 sticky top-24">
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-bold text-slate-700 dark:text-slate-200">Índice</h3>
            </div>
            <nav class="p-2 space-y-1" id="docs-nav">
                <!-- Introducción -->
                <button onclick="scrollToSection('intro')" class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-700 hover:text-emerald-600 dark:hover:text-emerald-400 hover:shadow-sm transition-all group active-nav">
                    <span class="text-slate-400 group-hover:text-emerald-500"><?= Icons::get('info', 'w-4 h-4') ?></span>
                    Introducción
                </button>
                <!-- Ventas -->
                <button onclick="scrollToSection('pos')" class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-700 hover:text-emerald-600 dark:hover:text-emerald-400 hover:shadow-sm transition-all group">
                        <span class="text-slate-400 group-hover:text-emerald-500"><?= Icons::get('shopping-cart', 'w-4 h-4') ?></span>
                    Punto de Venta
                </button>
                <!-- Inventario -->
                    <button onclick="scrollToSection('inventory')" class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-700 hover:text-emerald-600 dark:hover:text-emerald-400 hover:shadow-sm transition-all group">
                        <span class="text-slate-400 group-hover:text-emerald-500"><?= Icons::get('box', 'w-4 h-4') ?></span>
                    Inventario
                </button>
                <!-- Seguridad -->
                    <button onclick="scrollToSection('security')" class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-700 hover:text-emerald-600 dark:hover:text-emerald-400 hover:shadow-sm transition-all group">
                        <span class="text-slate-400 group-hover:text-emerald-500"><?= Icons::get('lock', 'w-4 h-4') ?></span>
                    Seguridad
                </button>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 min-w-0 space-y-8">
            
        <!-- Header Móvil (Solo visible < LG) -->
        <div class="lg:hidden mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Manual de Usuario</h1>
            <p class="text-slate-500 dark:text-slate-400">Todo lo que necesitas saber.</p>
        </div>

        <!-- Banner Principal (Desktop) -->
        <div class="hidden lg:block bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
            <div class="absolute right-0 top-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative z-10">
                <h1 class="text-3xl font-bold mb-2">Manual de Usuario</h1>
                <p class="text-emerald-100 max-w-lg">
                    Bienvenido al centro de documentación. Aquí encontrarás guías detalladas sobre cómo operar cada módulo de StockMaster Pro.
                </p>
            </div>
        </div>

        <!-- Content Blocks -->
        <div class="space-y-12 pb-12">
            
            <!-- Intro -->
            <section id="intro" class="scroll-mt-6">
                <div class="pb-6 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <?= Icons::get('star', 'w-5 h-5') ?>
                        </div>
                        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Lo Básico</h2>
                    </div>
                    
                    <div class="prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-400">
                        <p>
                            StockMaster Pro es un sistema diseñado para la <strong>velocidad</strong> y la <strong>confiabilidad offline</strong>.
                            A diferencia de otros sistemas web, este programa vive en tu computadora, por lo que no necesitas internet para facturar.
                        </p>
                        <div class="grid sm:grid-cols-3 gap-4 mt-6 not-prose">
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700">
                                <strong class="block text-slate-800 dark:text-slate-200 mb-1">1. Configura</strong>
                                <span class="text-sm">Ajusta tu empresa y tasas.</span>
                            </div>
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700">
                                <strong class="block text-slate-800 dark:text-slate-200 mb-1">2. Inventarea</strong>
                                <span class="text-sm">Carga tus productos.</span>
                            </div>
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700">
                                <strong class="block text-slate-800 dark:text-slate-200 mb-1">3. Vende</strong>
                                <span class="text-sm">Factura en segundos.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- POS -->
            <section id="pos" class="scroll-mt-6">
                <div class="pb-6 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <?= Icons::get('shopping-cart', 'w-5 h-5') ?>
                        </div>
                        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Punto de Venta (POS)</h2>
                    </div>
                    
                    <div class="prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-400">
                        <p>El POS está optimizado para uso con teclado y lector de código de barras.</p>
                        
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 mt-4 mb-2">Trucos Rápidos:</h4>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Usa el buscador global para encontrar productos por nombre o código.</li>
                            <li>La tasa de cambio se actualiza automáticamente si tienes internet al abrir la app, o puedes fijarla manualmente.</li>
                            <li>Presiona "Cobrar" para finalizar la venta e imprimir el ticket térmico.</li>
                        </ul>

                        <div class="mt-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4 rounded-lg flex gap-3 items-start not-prose">
                            <div class="text-indigo-600 dark:text-indigo-400 mt-0.5"><?= Icons::get('bulb', 'w-5 h-5') ?></div>
                            <div>
                                <strong class="block text-indigo-700 dark:text-indigo-300 text-sm">¿Ventas en Espera?</strong>
                                <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">
                                    Si un cliente va a buscar otro producto, no canceles la venta. Usa el botón "Suspender" para guardarla y retomarla después.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Inventory -->
            <section id="inventory" class="scroll-mt-6">
                <div class="pb-6 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <?= Icons::get('box', 'w-5 h-5') ?>
                        </div>
                        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Inventario</h2>
                    </div>
                    <div class="prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-400">
                        <p>Mantén el control de tus existencias. El sistema descuenta el stock automáticamente con cada venta.</p>
                        <p>Para cargar mercancía nueva, ve a <strong>Compras</strong>. Para ajustes manuales (pérdidas, consumo interno), ve a <strong>Movimientos</strong>.</p>
                    </div>
                </div>
            </section>

             <!-- Security -->
            <section id="security" class="scroll-mt-6">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center text-red-600 dark:text-red-400">
                            <?= Icons::get('lock', 'w-5 h-5') ?>
                        </div>
                        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Seguridad</h2>
                    </div>
                    <div class="prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-400">
                        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Recuperación de Acceso</h3>
                        <p>
                            Este sistema es seguro y cerrado. Si un usuario olvida su contraseña, <strong>no se envía por correo</strong> (ya que funciona offline).
                        </p>
                        <div class="mt-4 p-4 bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 not-prose">
                            <ol class="list-decimal pl-5 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                                <li>En el Login, clic en <span class="font-bold text-emerald-600">Recuperar clave</span>.</li>
                                <li>Aparecerá un <strong>Código de Solicitud</strong> (ej: <code>ABCD-1234</code>).</li>
                                <li>Envía ese código al Administrador del Sistema.</li>
                                <li>El Admin generará un <strong>Código de Respuesta</strong> único para ese momento.</li>
                                <li>Introdúcelo y tu clave se reseteará a <code>admin123</code>.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>
</div>

<script>
function scrollToSection(id) {
    const el = document.getElementById(id);
    if (el) {
        // Scroll suave
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Update Nav Active State
        document.querySelectorAll('#docs-nav button').forEach(btn => {
            btn.classList.remove('bg-white', 'dark:bg-slate-700', 'text-emerald-600', 'dark:text-emerald-400', 'shadow-sm');
            btn.classList.add('text-slate-600', 'dark:text-slate-400');
        });
        
        // Find the button that triggered this (naive approach)
        // Better: intersection observer, but for click this is fine
        const btn = document.querySelector(`button[onclick="scrollToSection('${id}')"]`);
        if(btn) {
            btn.classList.remove('text-slate-600', 'dark:text-slate-400');
            btn.classList.add('bg-white', 'dark:bg-slate-700', 'text-emerald-600', 'dark:text-emerald-400', 'shadow-sm');
        }
    }
}
</script>

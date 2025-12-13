<?php
/**
 * CREAR PRODUCTO - Vista Individual Enterprise
 * views/productos/crear.php
 */
use App\Helpers\Icons;
?>

<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
                <?= Icons::get('box', 'w-7 h-7 text-indigo-500') ?>
                Nuevo Producto
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Registra un nuevo artículo en tu inventario
            </p>
        </div>
        
        <a href="index.php?controlador=producto&accion=index" class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors font-medium">
            <?= Icons::get('arrow-left', 'w-4 h-4') ?>
            Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-6 sm:p-8 shadow-lg shadow-indigo-500/5">
        <form id="form-producto" action="index.php?controlador=producto&accion=crear" method="POST" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre del Producto</label>
                    <input type="text" name="nombre" placeholder="Ej: Harina PAN" required
                           class="w-full px-4 py-3 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 text-lg">
                </div>

                <!-- Categoría -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Categoría</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <?= Icons::get('tag', 'w-4 h-4 text-slate-400') ?>
                        </div>
                        <select name="categoria" required class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 appearance-none">
                            <option value="">Selecciona...</option>
                            <option value="Harinas">Harinas</option>
                            <option value="Panadería">Panadería</option>
                            <option value="Lácteos">Lácteos</option>
                            <option value="Carnes">Carnes</option>
                            <option value="Granos">Granos</option>
                            <option value="Bebidas">Bebidas</option>
                            <option value="Otros">Otros</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                             <?= Icons::get('chevron-down', 'w-4 h-4') ?>
                        </div>
                    </div>
                </div>

                <!-- Stock -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Stock Inicial</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <?= Icons::get('box', 'w-4 h-4 text-slate-400') ?>
                        </div>
                        <input type="number" name="stock" placeholder="0" min="0" required
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>

                <!-- Precio Compra -->
                <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-600">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Costo (USD)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold">$</span>
                            </div>
                            <input type="number" id="precio-compra" name="precio-compra" placeholder="0.00" step="0.01" min="0" required
                                   oninput="calcularPrecioVenta()"
                                   class="w-full pl-8 pr-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Margen (%)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold">%</span>
                            </div>
                            <input type="number" id="margen-ganancia" name="margen-ganancia" placeholder="30" min="0" required
                                   oninput="calcularPrecioVenta()"
                                   class="w-full pl-8 pr-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                    </div>
                    
                     <div class="col-span-1 md:col-span-2 pt-2 border-t border-slate-200 dark:border-slate-600 flex justify-between items-center">
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Precio Venta Estimado:</span>
                        <span id="precio-venta-calc" class="text-lg font-bold text-emerald-600 dark:text-emerald-400">$0.00</span>
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all text-lg">
                    <?= Icons::get('save', 'w-6 h-6') ?>
                    Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function calcularPrecioVenta() {
    const costo = parseFloat(document.getElementById('precio-compra').value) || 0;
    const margen = parseFloat(document.getElementById('margen-ganancia').value) || 0;
    
    if (costo > 0) {
        const venta = costo * (1 + (margen / 100));
        document.getElementById('precio-venta-calc').textContent = '$' + venta.toFixed(2);
    } else {
        document.getElementById('precio-venta-calc').textContent = '$0.00';
    }
}
</script>
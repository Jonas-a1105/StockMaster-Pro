<form id="form-agregar-producto" action="index.php?controlador=producto&accion=crear" method="POST" class="p-6 space-y-4">
    <?= \App\Helpers\Security::csrfField() ?>
    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre del Producto</label>
            <input type="text" name="nombre" required 
                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Código de Barras</label>
            <input type="text" name="codigo_barras" 
                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Categoría</label>
            <div class="relative z-50 group" id="combobox-categoria-add">
                <select name="categoria" id="categoria-select-hidden" class="sr-only" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                    <option value="Otros">Otros</option>
                </select>
                <div class="relative">
                    <input type="text" id="categoria-input-visual" placeholder="Seleccionar categoría..." readonly
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer">
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4 transition-transform duration-200" id="categoria-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <ul id="categoria-list-add" class="dropdown-list-floating divide-y divide-slate-100 dark:divide-slate-700"></ul>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Proveedor</label>
            <div class="relative z-50 group" id="combobox-proveedor-add">
                <select name="proveedor_id" id="proveedor-select-hidden" class="sr-only">
                    <option value="0">Sin proveedor</option>
                    <?php foreach ($proveedores as $prov): ?>
                        <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="relative">
                    <input type="text" id="proveedor-input-visual" placeholder="Seleccionar proveedor..." readonly
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer caret-emerald-500">
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4 transition-transform duration-200" id="proveedor-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <button type="button" id="btn-limpiar-prov-add" class="absolute inset-y-0 right-8 flex items-center px-2 text-slate-400 hover:text-red-500 hidden cursor-pointer z-10">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <ul id="proveedor-list-add" class="dropdown-list-floating divide-y divide-slate-100 dark:divide-slate-700"></ul>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Stock Inicial</label>
            <input type="number" name="stock" placeholder="0" min="0" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Precio Base (USD)</label>
            <input type="number" name="precio_base" step="0.01" min="0" required id="add-precio-base" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Margen (%)</label>
            <input type="number" name="margen_ganancia" placeholder="30" min="0" max="100" required id="add-margen" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        
        <div class="col-span-2">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="tiene_iva" id="add-tiene-iva" value="1" class="w-5 h-5 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500/30">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Aplicar IVA</span>
            </label>
        </div>
        
        <div id="add-iva-grupo" class="col-span-2 hidden">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Porcentaje IVA</label>
            <input type="number" name="iva_porcentaje" value="16" min="0" max="100" id="add-iva-porcentaje" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
    </div>
    
    <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">Vista previa de precios</p>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-xs text-slate-400">Compra</p>
                <p class="text-lg font-semibold text-slate-800 dark:text-white" id="add-preview-compra">$0.00</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Venta</p>
                <p class="text-lg font-semibold text-emerald-600" id="add-preview-venta">$0.00</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Ganancia</p>
                <p class="text-lg font-semibold text-blue-600" id="add-preview-ganancia">$0.00</p>
            </div>
        </div>
    </div>
</form>

<form id="form-editar-producto" action="index.php?controlador=producto&accion=actualizar" method="POST" class="p-6 space-y-4">
    <?= \App\Helpers\Security::csrfField() ?>
    <input type="hidden" name="id" id="editar-id">
    
    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre</label>
            <input type="text" name="nombre" id="editar-nombre" required 
                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Código</label>
            <input type="text" name="codigo_barras" id="editar-codigo-barras"
                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Proveedor</label>
            <div class="relative z-50 group" id="combobox-proveedor-edit">
                <select name="proveedor_id" id="editar-proveedor-hidden" class="sr-only">
                    <option value="0">Sin proveedor</option>
                    <?php foreach ($proveedores as $prov): ?>
                        <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="relative">
                    <input type="text" id="editar-proveedor-visual" placeholder="Seleccionar proveedor..." readonly
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer caret-emerald-500">
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4 transition-transform duration-200" id="proveedor-chevron-edit" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <button type="button" id="btn-limpiar-prov-edit" class="absolute inset-y-0 right-8 flex items-center px-2 text-slate-400 hover:text-red-500 hidden cursor-pointer z-10">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <ul id="proveedor-list-edit" class="dropdown-list-floating divide-y divide-slate-100 dark:divide-slate-700"></ul>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Stock Actual</label>
            <input type="number" name="stock" id="editar-stock" min="0" required
                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Precio Base (USD)</label>
            <input type="number" name="precio_base" id="editar-precio-base" step="0.01" min="0" required 
                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Margen (%)</label>
            <input type="number" name="margen_ganancia" id="editar-margen" min="0" max="100" required 
                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        
        <div class="col-span-2">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="tiene_iva" id="editar-tiene-iva" value="1" class="w-5 h-5 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500/30">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Aplicar IVA</span>
            </label>
        </div>
        
        <div id="editar-iva-grupo" class="col-span-2 hidden">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Porcentaje IVA</label>
            <input type="number" name="iva_porcentaje" id="editar-iva-porcentaje" value="16" min="0" max="100"
                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
    </div>
    
    <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-xs text-slate-400">Compra</p>
                <p class="text-lg font-semibold text-slate-800 dark:text-white" id="preview-precio-compra">$0.00</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Venta</p>
                <p class="text-lg font-semibold text-emerald-600" id="preview-precio-venta">$0.00</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Ganancia</p>
                <p class="text-lg font-semibold text-blue-600" id="preview-ganancia">$0.00</p>
            </div>
        </div>
    </div>
</form>

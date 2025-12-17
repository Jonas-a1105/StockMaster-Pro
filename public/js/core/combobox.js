/**
 * COMBOBOX.JS - Selector Buscable Reutilizable
 * =========================================================================
 * Maneja la lógica de los combobox personalizados (dropdowns con búsqueda).
 * Incluye optimización de rendimiento para listas largas.
 */

window.setupCombobox = function (wrapperId, hiddenSelectId, inputVisualId, listId, btnLimpiarId, config = {}) {
    const wrapper = document.getElementById(wrapperId);
    if (!wrapper) {
        console.warn(`[Combobox] Wrapper not found: ${wrapperId}`);
        return;
    }

    // Evitar doble inicialización
    if (wrapper.dataset.comboboxInitialized === 'true') {
        console.log(`[Combobox] Already initialized: ${wrapperId}`);
        return;
    }
    wrapper.dataset.comboboxInitialized = 'true';

    const hiddenSelect = document.getElementById(hiddenSelectId);
    const inputVisual = document.getElementById(inputVisualId);
    const list = document.getElementById(listId);
    const btnLimpiar = btnLimpiarId ? document.getElementById(btnLimpiarId) : null;

    // Verificar elementos requeridos
    if (!hiddenSelect || !inputVisual || !list) {
        console.warn(`[Combobox] Missing elements for ${wrapperId}:`, {
            hiddenSelect: !!hiddenSelect,
            inputVisual: !!inputVisual,
            list: !!list
        });
        return;
    }

    // Configuración por defecto
    const dataSource = config.dataSource || [];
    const onSelect = config.onSelect || (() => { });
    const searchable = config.searchable !== false; // Por defecto es buscable

    console.log(`[Combobox] Setup for ${inputVisualId}. items: ${dataSource.length}, searchable: ${searchable}`);

    // 1. Initial Render
    renderList('');

    // 2. Event Listeners
    inputVisual.addEventListener('click', () => {
        list.classList.toggle('hidden');
        if (!list.classList.contains('hidden')) {
            renderList('');
            if (searchable) inputVisual.focus();
        }
    });

    // Solo agregar listener de input si es buscable
    if (searchable) {
        inputVisual.addEventListener('input', (e) => {
            const term = e.target.value;
            list.classList.remove('hidden');
            renderList(term);

            // Show clear button if text exists
            if (btnLimpiar) btnLimpiar.classList.toggle('hidden', term === '');
        });
    }

    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            list.classList.add('hidden');
        }
    });

    // Clear Button (solo si existe)
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', (e) => {
            e.stopPropagation();
            inputVisual.value = '';
            hiddenSelect.value = '0';
            btnLimpiar.classList.add('hidden');
            renderList('');
            list.classList.remove('hidden');
            inputVisual.focus();
            onSelect(null);
        });
    }

    function renderList(term) {
        list.innerHTML = '';
        const lowerTerm = term.toLowerCase();

        // Always include 'Sin selección' if matching or term empty
        // Custom label can be passed via config, default 'Sin proveedor' (legacy) or generic 'Ninguno'
        const defaultLabel = config.defaultLabel || 'Sin selección';

        if (defaultLabel.toLowerCase().includes(lowerTerm)) {
            addItem({ id: '0', nombre: defaultLabel });
        }

        const matches = dataSource.filter(item => {
            const nombre = item.nombre || item.name || ''; // Support 'nombre' or 'name'
            return String(nombre).toLowerCase().includes(lowerTerm);
        });

        // Debugging
        // console.log(`Search term: "${lowerTerm}", Matches: ${matches.length}`);

        if (matches.length === 0 && lowerTerm !== '' && !defaultLabel.toLowerCase().includes(lowerTerm)) {
            list.innerHTML += `<li class="px-4 py-3 text-sm text-slate-500 text-center">No encontrado</li>`;
        } else {
            // OPTIMIZATION: Limit items to prevent lag
            const MAX_ITEMS = 50;
            const limitedMatches = matches.slice(0, MAX_ITEMS);

            limitedMatches.forEach(p => addItem(p));

            if (matches.length > MAX_ITEMS) {
                list.innerHTML += `
                    <li class="px-4 py-2 text-xs text-slate-400 text-center italic border-t border-slate-100 dark:border-slate-700 pointer-events-none">
                        Mostrando ${MAX_ITEMS} de ${matches.length} resultados. Escribe para filtrar...
                    </li>
                `;
            }
        }
    }

    function addItem(p) {
        const li = document.createElement('li');
        li.className = 'px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-700 cursor-pointer transition-colors flex items-center justify-between group';

        const isSelected = hiddenSelect.value == p.id;

        if (isSelected) {
            li.classList.add('bg-emerald-50', 'dark:bg-emerald-900/20', 'text-emerald-700', 'dark:text-emerald-400');
        }

        const displayName = p.nombre || p.name || 'Sin nombre';
        li.innerHTML = `<span>${displayName}</span>`;
        if (isSelected) {
            li.innerHTML += `<svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
        }

        li.onclick = () => {
            selectItem(p);
        };
        list.appendChild(li);
    }

    function selectItem(p) {
        hiddenSelect.value = p.id;
        inputVisual.value = p.nombre;
        list.classList.add('hidden');
        if (btnLimpiar && p.id !== '0') btnLimpiar.classList.remove('hidden');
        onSelect(p);
    }

    // API Publica para setear valor externamente (ej: al editar)
    wrapper.setComboboxValue = function (id) {
        hiddenSelect.value = id;
        const p = dataSource.find(x => x.id == id);
        if (p) {
            inputVisual.value = p.nombre;
            if (btnLimpiar) btnLimpiar.classList.remove('hidden');
        } else {
            inputVisual.value = config.defaultLabel || 'Sin selección';
            if (btnLimpiar) btnLimpiar.classList.add('hidden');
            if (id == 0) hiddenSelect.value = 0;
        }
    };
};

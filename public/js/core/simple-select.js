/**
 * SIMPLE-SELECT.JS - Selector Visual Moderno
 * =========================================================================
 * Reemplaza visualmente los <select> nativos con un dropdown estilizado.
 * Mantiene la funcionalidad original (sync de valor y eventos change).
 */

window.setupSimpleSelect = function (target, config = {}) {
    const originalSelect = typeof target === 'string' ? document.getElementById(target) : target;
    if (!originalSelect) return;

    // Evitar doble inicialización
    if (originalSelect.dataset.simpleSelectInitialized) return;
    originalSelect.dataset.simpleSelectInitialized = "true";

    // Ocultar select original pero mantenerlo en el DOM para forms
    originalSelect.classList.add('hidden');
    // Asegurar que si tiene clases de ancho, el wrapper las herede o maneje
    const wrapperClasses = originalSelect.className.replace('hidden', '').trim();

    // Crear estructura DOM
    const wrapper = document.createElement('div');
    wrapper.className = `relative inline-block align-middle ${config.fullWidth ? 'w-full' : ''}`;

    // Botón Trigger
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `
        flex items-center justify-between gap-2 px-4 py-2.5 
        bg-white dark:bg-slate-700/50 
        border border-slate-200 dark:border-slate-600 
        rounded-xl text-slate-700 dark:text-slate-200 
        hover:bg-slate-50 dark:hover:bg-slate-600/50 
        focus:outline-none focus:ring-2 focus:ring-indigo-500/20 
        transition-all text-sm font-medium
        ${config.fullWidth ? 'w-full' : ''}
    `;

    // Transferir clases de ancho y layout al wrapper
    const widthClasses = Array.from(originalSelect.classList).filter(cls =>
        cls.startsWith('w-') || cls.startsWith('flex-') || cls === 'grow' || cls === 'shrink'
    );
    if (widthClasses.length > 0) {
        wrapper.classList.add(...widthClasses);
        // Si hay clases de ancho, aseguramos que el botón tome el ancho completo del wrapper
        button.classList.add('w-full');
    } else if (config.fullWidth) {
        wrapper.classList.add('w-full');
        button.classList.add('w-full');
    }

    // Dropdown List
    const list = document.createElement('ul');
    list.className = `
        absolute mt-1 w-full min-w-[150px]
        bg-white dark:bg-slate-800 
        rounded-xl shadow-xl 
        border border-slate-200 dark:border-slate-700 
        max-h-60 overflow-y-auto 
        transform origin-top transition-all duration-200 
        opacity-0 scale-95 pointer-events-none
    `;
    // Force Z-Index via style to avoid Tailwind JIT issues
    list.style.zIndex = '99999';

    // Posicionamiento inteligente (por ahora simple absolute)
    if (config.alignRight) list.classList.add('right-0');
    else list.classList.add('left-0');

    // Estado interno
    let isOpen = false;

    // Función: Actualizar texto del botón
    const updateButtonText = () => {
        const selectedOption = originalSelect.options[originalSelect.selectedIndex];
        button.innerHTML = `
            <span class="truncate">${selectedOption ? selectedOption.text : 'Selecciona...'}</span>
            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        `;
    };

    // Insertar en DOM - El wrapper va en el lugar original
    originalSelect.parentNode.insertBefore(wrapper, originalSelect);
    // El select original se queda oculto dentro del wrapper por conveniencia de posición en DOM
    wrapper.appendChild(originalSelect);
    wrapper.appendChild(button);
    // IMPORTANTE: La lista NO se inserta todavía. Se insertará en el BODY al abrir.

    // Sync inicial
    updateButtonText();
    // No construimos options todavía, se construyen on demand o se preparan pero no se insertan

    // Escuchar cambios externos en el select original
    originalSelect.addEventListener('change', () => {
        updateButtonText();
    });

    // === LOGICA FIXED / PORTAL ===
    const toggleList = (forceState) => {
        isOpen = forceState !== undefined ? forceState : !isOpen;

        if (isOpen) {
            // 1. Cerrar otros
            document.querySelectorAll('.simple-select-open').forEach(el => {
                if (el !== wrapper) el.dispatchEvent(new CustomEvent('close-select'));
            });

            // 2. Construir lista (en memoria)
            buildOptions();

            // 3. Calcular Posición Fixed y aplicar estilos ANTES de insertar al DOM para evitar parpadeos
            const rect = button.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            // Fix width and remove expansion classes immediately
            list.style.width = `${rect.width}px`;
            list.style.left = `${rect.left}px`;

            // Reset base classes
            list.classList.remove('top-full', 'mt-1', 'bottom-full', 'mb-1', 'absolute', 'w-full');
            list.classList.add('fixed');

            // Height check logic (approximate)
            const listHeight = Math.min(originalSelect.options.length * 36 + 10, 250);

            if (spaceBelow < listHeight && spaceAbove > spaceBelow) {
                // Abrir hacia arriba
                list.style.top = 'auto';
                list.style.bottom = `${window.innerHeight - rect.top + 4}px`;
                list.classList.add('origin-bottom');
            } else {
                // Abrir hacia abajo
                list.style.bottom = 'auto';
                list.style.top = `${rect.bottom + 4}px`;
                list.classList.add('origin-top');
            }

            // 4. Insertar en body ya estilizado
            document.body.appendChild(list);

            // 5. Animar entrada
            // Force reflow
            void list.offsetWidth;
            list.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
            list.classList.add('opacity-100', 'scale-100', 'pointer-events-auto');

            wrapper.classList.add('simple-select-open');

            // 6. Escuchar Scroll/Resize
            window.addEventListener('scroll', closeOnScroll, true);
            window.addEventListener('resize', closeOnScroll);

        } else {
            // Cerrar
            list.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
            list.classList.add('opacity-0', 'scale-95', 'pointer-events-none');

            wrapper.classList.remove('simple-select-open');

            window.removeEventListener('scroll', closeOnScroll, true);
            window.removeEventListener('resize', closeOnScroll);

            // Remover del DOM tras animación
            setTimeout(() => {
                if (list.parentNode === document.body && !isOpen) {
                    document.body.removeChild(list);
                }
            }, 200);
        }
        updateButtonText();
    };

    // Construir opciones
    const buildOptions = () => {
        list.innerHTML = '';
        Array.from(originalSelect.options).forEach((opt, index) => {
            const li = document.createElement('li');
            li.className = `
                px-4 py-2 text-sm cursor-pointer transition-colors
                ${opt.selected
                    ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 font-medium'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50'}
            `;
            li.textContent = opt.text;

            li.onclick = (e) => {
                e.stopPropagation();
                originalSelect.selectedIndex = index;
                originalSelect.dispatchEvent(new Event('change')); // Disparar evento nativo
                toggleList(false);
                buildOptions(); // Reconstruir para actualizar estado visual (check/color)
            };

            list.appendChild(li);
        });
    };

    const closeOnScroll = (e) => {
        // Si el scroll ocurre DENTRO de la lista, no cerrar
        // Verificar que e.target sea un nodo DOM válido antes de usar contains()
        if (e && e.target && e.target.nodeType === Node.ELEMENT_NODE && list.contains(e.target)) return;
        if (isOpen) toggleList(false);
    };

    // Listeners
    button.onclick = (e) => {
        e.stopPropagation();
        toggleList();
    };

    // Método público para cerrar (usado por cleanup global)
    wrapper.simpleSelectClose = () => {
        if (isOpen) toggleList(false);
    };
};

// Función de inicialización para selects visibles
const initSimpleSelects = () => {
    document.querySelectorAll('[data-setup-simple-select]').forEach(el => {
        // Solo inicializar si el elemento es visible (no está en un modal oculto)
        if (isElementVisible(el)) {
            window.setupSimpleSelect(el, {
                fullWidth: el.classList.contains('w-full'),
                alignRight: el.dataset.align === 'right'
            });
        }
    });
};

// Función para inicializar selects dentro de un contenedor específico
const initSimpleSelectsInContainer = (container) => {
    if (!container) return;
    container.querySelectorAll('[data-setup-simple-select]').forEach(el => {
        if (!el.dataset.simpleSelectInitialized) {
            window.setupSimpleSelect(el, {
                fullWidth: el.classList.contains('w-full'),
                alignRight: el.dataset.align === 'right'
            });
        }
    });
};

// Verificar si un elemento es visible
const isElementVisible = (el) => {
    if (!el) return false;
    // Verificar que no esté dentro de un elemento con clase 'hidden'
    let parent = el.parentElement;
    while (parent) {
        if (parent.classList.contains('hidden')) return false;
        parent = parent.parentElement;
    }
    return true;
};

// Observador para detectar cuando modales se abren (pierden clase 'hidden')
const setupModalObserver = () => {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                // Si el elemento tenía 'hidden' y ahora no lo tiene, es un modal que se abrió
                if (target.id && target.id.startsWith('modal-') && !target.classList.contains('hidden')) {
                    // Delay pequeño para asegurar que el modal esté completamente visible
                    setTimeout(() => {
                        initSimpleSelectsInContainer(target);
                    }, 300);
                }
            }
        });
    });

    // Observar todos los modales existentes
    document.querySelectorAll('[id^="modal-"]').forEach(modal => {
        observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
    });
};

// Limpieza global de dropdowns abiertos (útil para navegación SPA/Turbo)
const cleanupSimpleSelects = () => {
    // Buscar todos lo selects abiertos y cerrarlos forzosamente
    document.querySelectorAll('.simple-select-open').forEach(wrapper => {
        if (wrapper.simpleSelectClose) wrapper.simpleSelectClose();
    });
    // Eliminar cualquier lista huérfana en body
    const orphans = document.body.querySelectorAll('ul.fixed.z-\\[150\\]');
    orphans.forEach(ul => ul.remove());
};

// Auto-inicializar en carga inicial y navegación TurboNav
document.addEventListener('DOMContentLoaded', () => {
    initSimpleSelects();
    setupModalObserver();
});
window.addEventListener('app:page-loaded', () => {
    initSimpleSelects();
    setupModalObserver();
});

// Escuchar evento de modal abierto para inicializar simple-selects dentro
document.addEventListener('modal:opened', (e) => {
    const modal = e.target;
    if (modal) {
        initSimpleSelectsInContainer(modal);
    }
});

// Limpiar al navegar fuera
document.addEventListener('turbo:before-cache', cleanupSimpleSelects);
document.addEventListener('turbo:visit', cleanupSimpleSelects);
window.addEventListener('app:page-unloaded', cleanupSimpleSelects);

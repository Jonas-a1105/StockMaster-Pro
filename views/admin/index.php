<?php
/**
 * ADMINISTRACIÓN USUARIOS - Vista Enterprise
 * views/admin/index.php
 */
use App\Helpers\Icons;

$usuarios = $usuarios ?? [];
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('team', 'w-7 h-7 text-indigo-500') ?>
            Administración de Usuarios
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Gestión de usuarios y planes de suscripción
        </p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="index.php?controlador=admin&accion=tickets" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
            <?= Icons::get('ticket', 'w-4 h-4') ?>
            <span>Tickets de Soporte</span>
        </a>
    </div>
</div>

<!-- Tabla de Usuarios -->
<?php
$headers = ['Usuario / Email', 'Plan Actual', 'Estado / Vencimiento', 'Gestionar Plan'];
$rows = [];

foreach ($usuarios as $usuario) {
    // Columna 1: Usuario / Email
    $col1 = '<div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 font-bold border border-slate-200 dark:border-slate-600">
                    ' . strtoupper(substr($usuario['email'], 0, 1)) . '
                </div>
                <div>
                    <p class="font-medium text-slate-800 dark:text-white">' . htmlspecialchars($usuario['email']) . '</p>
                    <p class="text-xs text-slate-500">ID: ' . $usuario['id'] . ' • Reg: ' . (new DateTime($usuario['fecha_registro']))->format('d/M Y') . '</p>
                </div>
            </div>';

    // Columna 2: Plan Actual
    if ($usuario['plan'] === 'premium') {
        $col2 = '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">' . Icons::get('crown', 'w-3 h-3') . ' Premium</span>';
    } else {
        $col2 = '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400 border border-slate-200 dark:border-slate-600">Free</span>';
    }

    // Columna 3: Estado / Vencimiento
    $col3 = '';
    if ($usuario['plan'] === 'free') {
        $col3 = '<span class="text-slate-400 text-xs">—</span>';
    } elseif ($usuario['trial_ends_at'] === null) {
        $col3 = '<span class="text-emerald-600 font-medium flex items-center gap-1.5 text-xs">' . Icons::get('check-circle', 'w-3.5 h-3.5') . ' Permanente</span>';
    } else {
        $fecha = new DateTime($usuario['trial_ends_at']);
        $hoy = new DateTime();
        $diff = $hoy->diff($fecha);
        if ($fecha < $hoy) {
            $col3 = '<div class="flex items-center gap-1.5 text-red-600 text-xs font-medium">' . Icons::get('error', 'w-3.5 h-3.5') . ' Vencido</div>
                     <div class="text-xs text-slate-400 mt-0.5">Venció el ' . $fecha->format('d/m/Y') . '</div>';
        } else {
            $color = $diff->days < 5 ? 'text-amber-600' : 'text-slate-600 dark:text-slate-300';
            $col3 = '<div class="flex flex-col">
                        <span class="' . $color . ' font-medium text-sm">' . $diff->days . ' días restantes</span>
                        <span class="text-xs text-slate-400">Vence: ' . $fecha->format('d/m/Y') . '</span>
                    </div>';
        }
    }

    // Columna 4: Gestionar Plan
    $col4 = '<form action="index.php?controlador=admin&accion=cambiarPlan" method="POST" class="flex items-center justify-center gap-2">
                ' . \App\Helpers\Security::csrfField() . '
                <input type="hidden" name="usuario_id" value="' . $usuario['id'] . '">';
    
    if ($usuario['plan'] === 'free') {
        $col4 .= '<input type="hidden" name="accion" value="premium">
                  <select name="duracion" class="appearance-none pl-3 pr-8 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-xs font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                      <option value="15">15 Días</option>
                      <option value="30" selected>1 Mes</option>
                      <option value="90">3 Meses</option>
                      <option value="365">1 Año</option>
                      <option value="permanent">Permanente</option>
                  </select>
                  <button type="submit" class="p-1.5 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition-colors" title="Activar Premium">
                      ' . Icons::get('check', 'w-4 h-4') . '
                  </button>';
    } else {
        $col4 .= '<input type="hidden" name="accion" value="free">
                  <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-xs font-medium" onclick="return confirm(\'¿Estás seguro de quitar el plan Premium?\');">
                      ' . Icons::get('arrow-down', 'w-3.5 h-3.5') . '
                      Degradar a Free
                  </button>';
    }
    $col4 .= '</form>';

    $rows[] = [
        'content' => [
            \App\Core\View::raw($col1), 
            ['content' => \App\Core\View::raw($col2), 'class' => 'text-center'], 
            \App\Core\View::raw($col3), 
            ['content' => \App\Core\View::raw($col4), 'class' => 'text-center']
        ]
    ];
}

echo App\Core\View::component('table', [
    'headers' => $headers,
    'rows' => $rows,
    'emptyMessage' => 'No hay usuarios certificados en el sistema.',
    'emptyIcon' => 'user'
]);
?>

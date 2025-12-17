<?php
use App\Helpers\Icons;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar Licencia - Enterprise System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-gray-200">
        
        <!-- Header -->
        <div class="bg-indigo-600 p-8 text-center">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                <?= Icons::get('lock', 'w-8 h-8 text-white') ?>
            </div>
            <h1 class="text-2xl font-bold text-white mb-1">Activación Requerida</h1>
            <p class="text-indigo-200 text-sm">Tu licencia ha expirado o no es válida.</p>
        </div>

        <div class="p-8">
            <!-- Flash Messages -->
            <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm font-medium mb-6 flex items-center gap-2 border border-red-100">
                    <?= Icons::get('error', 'w-5 h-5') ?>
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <form action="index.php?controlador=license&accion=activar" method="POST" class="space-y-6">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Clave de Licencia</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <?= Icons::get('key', 'w-5 h-5 text-gray-400') ?>
                        </div>
                        <input type="text" name="license_key" required placeholder="XXXX-XXXX-XXXX-XXXX"
                               class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 font-mono uppercase tracking-wide">
                    </div>
                </div>

                <div class="text-sm text-gray-500 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <h4 class="font-semibold text-gray-700 mb-1 flex items-center gap-2">
                        <?= Icons::get('info', 'w-4 h-4') ?>
                        ¿Cómo obtengo una clave?
                    </h4>
                    <p>Contacta al administrador del sistema para renovar tu suscripción y recibir un código de activación.</p>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2">
                    <?= Icons::get('check-circle', 'w-5 h-5') ?>
                    Validar y Activar
                </button>
            </form>
            
            <div class="mt-6 text-center">
                 <a href="index.php?controlador=login&accion=logout" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
                    Cerrar sesión actual
                </a>
            </div>
        </div>
    </div>

</body>
</html>

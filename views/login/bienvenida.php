<?php use App\Helpers\Icons; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenid@ | StockMaster Pro</title>
    <link rel="shortcut icon" href="img/StockMasterPro.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/main.css?v=<?= time() ?>">
    <link href="css/animations.css" rel="stylesheet">
    <meta http-equiv="refresh" content="2.0;url=<?= $redirectUrl ?? 'index.php?controlador=dashboard' ?>">
</head>
<body class="min-h-screen bg-[#eef2f6] flex flex-col items-center justify-center p-4">

    <!-- Splash Screen Content -->
    <div class="bg-white rounded-[32px] p-12 shadow-2xl shadow-slate-200/50 border border-slate-200 flex flex-col items-center text-center max-w-sm w-full animate-in fade-in zoom-in-95 duration-500">
        
        <!-- Logo Animado -->
        <div class="relative mb-8 group">
            <div class="absolute inset-0 bg-emerald-100 rounded-full animate-ping opacity-20 duration-1000"></div>
            <div class="relative w-28 h-28 bg-white rounded-full border-4 border-slate-50 shadow-lg flex items-center justify-center overflow-hidden animate-bounce">
                <div class="w-full h-full bg-gradient-to-tr from-emerald-50 to-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-full h-full p-4" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="grad_stock_welcome" x1="0" y1="64" x2="0" y2="0" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#D1FAE5"/>
                                <stop offset="1" stop-color="#34D399"/>
                            </linearGradient>
                            <linearGradient id="grad_growth_welcome" x1="10" y1="50" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#10B981"/>
                                <stop offset="1" stop-color="#047857"/>
                            </linearGradient>
                            <filter id="shadow_welcome" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="3" stdDeviation="2" flood-color="#064E3B" flood-opacity="0.2"/>
                            </filter>
                        </defs>
                        <rect x="12" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_welcome)" opacity="0.5"/>
                        <rect x="12" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_welcome)" opacity="0.6"/>
                        <rect x="26" y="30" width="10" height="7" rx="1" fill="url(#grad_stock_welcome)" opacity="0.7"/>
                        <rect x="26" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_welcome)" opacity="0.8"/>
                        <rect x="26" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_welcome)" opacity="0.9"/>
                        <rect x="40" y="22" width="10" height="7" rx="1" fill="url(#grad_stock_welcome)" />
                        <rect x="40" y="30" width="10" height="7" rx="1" fill="url(#grad_stock_welcome)" />
                        <rect x="40" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_welcome)" />
                        <rect x="40" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_welcome)" />
                        <circle cx="10" cy="50" r="5" fill="#10B981" stroke="white" stroke-width="1.5" filter="url(#shadow_welcome)" />
                        <path d="M10 47V53M7 50H13" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M10 50 C 18 50, 24 35, 34 38 C 42 40, 46 25, 54 12" stroke="url(#grad_growth_welcome)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none" filter="url(#shadow_welcome)"/>
                        <path d="M54 12 C 54 12, 44 14, 42 22 C 42 22, 58 24, 60 14 C 60 14, 58 8, 54 12 Z" fill="#10B981" stroke="white" stroke-width="1" filter="url(#shadow_welcome)"/>
                    </svg>
                </div>
            </div>
            <div class="absolute bottom-1 right-2 w-7 h-7 bg-emerald-500 border-4 border-white rounded-full shadow-sm"></div>
        </div>

        <h2 class="text-2xl font-bold text-slate-800 mb-2">
            <?= htmlspecialchars($mensaje ?? '¡Bienvenido al Sistema!') ?>
        </h2>
        
        <p class="text-sm text-slate-500 font-medium mb-10 px-4 leading-relaxed">
            Preparando tu entorno de trabajo...
        </p>

        <!-- Spinner -->
        <div class="flex flex-col items-center gap-3">
            <div class="relative">
                <div class="w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-2 h-2 bg-emerald-500 rounded-full opacity-50"></div>
            </div>
            <p class="text-[10px] text-slate-400 font-bold tracking-widest uppercase animate-pulse">Cargando módulos</p>
        </div>

    </div>

</body>
</html>
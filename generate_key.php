<?php
/**
 * GENERADOR DE CLAVES DE LICENCIA (Híbrido: CLI y Web)
 * ⚠️ IMPORTANTE: Mantener este archivo seguro. No subir al servidor del cliente final.
 */

// Configuración
define('SECRET_KEY', 'ENTERPRISE_SECRET_KEY_v2025_SECURE'); // Debe coincidir con LicenseHelper

function generarLicencia($dias, $unidad, $cliente) {
    if ($dias < 1) $dias = 1;
    $payload = [
        'dias' => (int)$dias,
        'unidad' => $unidad,
        'creado' => time(),
        'cliente' => strip_tags($cliente)
    ];

    $payloadJson = json_encode($payload);
    $payloadBase64 = base64_encode($payloadJson);
    $signature = hash_hmac('sha256', $payloadBase64, SECRET_KEY);
    
    return $payloadBase64 . '.' . $signature;
}

// Lógica de Procesamiento
$generatedKey = '';
$message = '';
$diasInput = 30; // Default for form

// MODO CLI
if (php_sapi_name() === 'cli') {
    $dias = isset($argv[1]) ? (int)$argv[1] : 30;
    $cliente = isset($argv[2]) ? $argv[2] : 'CLI_User';
    
    echo "Generando licencia para $dias dias (Cliente: $cliente)...\n";
    $key = generarLicencia($dias, $cliente);
    echo "\n--------------------------------------------------\n";
    echo "CLAVE DE ACTIVACION:\n$key\n";
    echo "--------------------------------------------------\n\n";
    exit;
}

// MODO WEB
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Asegurar que el valor viene del POST
    $diasInput = $_POST['dias'] ?? 30;
    
    // Lógica para detectar unidades
    if ($diasInput === '1_minute') {
        $dias = 1;
        $unidad = 'minutes';
    } else {
        $dias = (int)$diasInput;
        $unidad = 'days';
    }

    $cliente = $_POST['cliente'] ?? 'Web User';
    
    // Debug preventivo (puedes borrarlo después, pero útil)
    // echo "Debug: Días recibidos: " . $dias; 

    $generatedKey = generarLicencia($dias, $unidad, $cliente);
    $message = "Licencia generada exitosamente para $cliente ($dias $unidad).";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Licencias - Admin Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-900 min-h-screen text-gray-100 p-6 flex items-center justify-center">

    <div class="max-w-2xl w-full">
        <!-- Warning Banner -->
        <div class="bg-amber-500/10 border border-amber-500/50 rounded-lg p-4 mb-6 text-amber-500 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <strong class="block mb-1">Herramienta Administrativa Privada</strong>
                <p>Esta herramienta genera llaves maestras para el software. <strong>No subas este archivo</strong> al servidor de tus clientes. Mantenlo solo en tu entorno de desarrollo local.</p>
            </div>
        </div>

        <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl overflow-hidden p-8">
            <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                <svg class="w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Generador de Licencias
            </h1>
            <p class="text-gray-400 mb-8">Crea códigos de activación firmados para tus clientes.</p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                
                <!-- Formulario -->
                <div>
                    <form method="POST" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Nombre del Cliente / Referencia</label>
                            <input type="text" name="cliente" required placeholder="Ej: Empresa S.A." value="<?= isset($_POST['cliente']) ? htmlspecialchars($_POST['cliente']) : '' ?>"
                                   class="w-full bg-gray-900 border border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Duración (Días)</label>
                            <select name="dias" class="w-full bg-gray-900 border border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                                <option value="1_minute" <?= ($diasInput == '1_minute') ? 'selected' : '' ?>>⏱️ 1 Minuto (TEST)</option>
                                <option value="7" <?= ($diasInput == 7) ? 'selected' : '' ?>>7 Días (Trial)</option>
                                <option value="15" <?= ($diasInput == 15) ? 'selected' : '' ?>>15 Días</option>
                                <option value="30" <?= ($diasInput == 30) ? 'selected' : '' ?>>30 Días (1 Mes)</option>
                                <option value="90" <?= ($diasInput == 90) ? 'selected' : '' ?>>90 Días (3 Meses)</option>
                                <option value="180" <?= ($diasInput == 180) ? 'selected' : '' ?>>180 Días (6 Meses)</option>
                                <option value="365" <?= ($diasInput == 365) ? 'selected' : '' ?>>365 Días (1 Año)</option>
                                <option value="3650" <?= ($diasInput == 3650) ? 'selected' : '' ?>>Permanente (10 Años)</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-500/20 transition-all transform hover:scale-[1.02]">
                            Generar Licencia
                        </button>
                    </form>
                </div>

                <!-- Resultado -->
                <div class="bg-gray-900 rounded-xl p-6 border border-gray-700 flex flex-col justify-center relative">
                    <?php if ($generatedKey): ?>
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-500 mb-4">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="text-white font-bold text-lg mb-4">¡Licencia Generada!</h3>
                            
                            <div class="relative group">
                                <textarea id="licenseOutput" readonly class="w-full h-32 bg-gray-800 border border-gray-600 rounded-lg p-3 text-xs text-gray-300 font-mono break-all resize-none focus:outline-none"><?= $generatedKey ?></textarea>
                                
                                <button onclick="copyToClipboard()" class="absolute top-2 right-2 p-2 bg-gray-700 hover:bg-gray-600 text-white rounded-md transition-colors opacity-0 group-hover:opacity-100" title="Copiar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Copia y envía este código al cliente.</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <p>Completa el formulario para generar una nueva clave.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-6 text-gray-600 text-xs">
            &copy; 2025 Enterprise Software Solutions
        </div>
    </div>

    <script>
        function copyToClipboard() {
            const copyText = document.getElementById("licenseOutput");
            copyText.select();
            copyText.setSelectionRange(0, 99999); 
            navigator.clipboard.writeText(copyText.value).then(() => {
                alert("Licencia copiada al portapapeles");
            });
        }
    </script>
</body>
</html>

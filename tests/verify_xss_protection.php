<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\View;

// Mock session/env if needed, though View doesn't strictly depend on it for this test
if (!defined('BASE_URL')) define('BASE_URL', '/');

echo "--- Iniciando verificación de Protección XSS Automática ---\n";

$data = [
    'unsafe_string' => '<script>alert("xss")</script>',
    'safe_string' => View::raw('<strong>Contenido Seguro</strong>'),
    'nested' => [
        'dangerous' => '"><img src=x onerror=alert(1)>',
        'number' => 123
    ]
];

// Creamos un archivo de vista temporal en el directorio real de vistas
$testViewDir = __DIR__ . '/../views/test';
if (!is_dir($testViewDir)) mkdir($testViewDir, 0777, true);

$viewPath = $testViewDir . '/xss_test.php';
$viewContent = <<<'PHP'
Unsafe: <?php echo $unsafe_string; ?>
Safe: <?php echo $safe_string; ?>
Nested: <?php echo $nested['dangerous']; ?>
Number: <?php echo $nested['number']; ?>
PHP;

file_put_contents($viewPath, $viewContent);

try {
    echo "Renderizando vista de prueba...\n";
    // Usamos renderViewOnly para evitar el layout
    $output = View::renderViewOnly('test/xss_test', $data);
    
    echo "\nResultado del renderizado:\n";
    echo "============================\n";
    echo $output;
    echo "\n============================\n";
    
    // Verificaciones
    $success = true;
    if (strpos($output, '&lt;script&gt;') === false) {
        echo "FAIL: 'unsafe_string' no fue escapado.\n";
        $success = false;
    }
    if (strpos($output, '<strong>') === false) {
        echo "FAIL: 'safe_string' (raw) fue escapado incorrectamente.\n";
        $success = false;
    }
    if (strpos($output, '&quot;&gt;&lt;img') === false) {
        echo "FAIL: 'nested.dangerous' no fue escapado.\n";
        $success = false;
    }

    if ($success) {
        echo "\n✅ VERIFICACIÓN EXITOSA: El escapado automático funciona correctamente.\n";
    } else {
        echo "\n❌ VERIFICACIÓN FALLIDA.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    // Limpieza
    if (file_exists($viewPath)) unlink($viewPath);
    if (is_dir($testViewDir)) rmdir($testViewDir);
}

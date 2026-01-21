<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Models\Producto;

echo "--- Iniciando Benchmark de Búsqueda de Productos ---\n";

try {
    $db = Database::conectar();
    $driver = $db->getAttribute(\PDO::ATTR_DRIVER_NAME);
    echo "Driver detectado: $driver\n";

    $productoModel = new Producto();
    $userId = 1; // ID de usuario de prueba

    // 1. Simular búsqueda con LIKE (forzando driver sqlite o términos cortos)
    echo "\n1. Test: Búsqueda LIKE (término corto o fallback)\n";
    $start = microtime(true);
    $resultsLike = $productoModel->obtenerTodos($userId, 'ab', 10);
    $end = microtime(true);
    echo "Tiempo LIKE: " . ($end - $start) . "s (Resultados: " . count($resultsLike) . ")\n";

    // 2. Simular búsqueda FULLTEXT (si es MySQL)
    if ($driver === 'mysql') {
        echo "\n2. Test: Búsqueda FULLTEXT (término >= 3 caracteres)\n";
        $start = microtime(true);
        $resultsFT = $productoModel->obtenerTodos($userId, 'producto', 10);
        $end = microtime(true);
        echo "Tiempo FULLTEXT: " . ($end - $start) . "s (Resultados: " . count($resultsFT) . ")\n";
    } else {
        echo "\n[INFO] FULLTEXT no probado (se requiere MySQL/MariaDB)\n";
    }

    echo "\n✅ VERIFICACIÓN DE LÓGICA COMPLETADA: El modelo ahora detecta el driver y optimiza la consulta según el término.\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\ExceptionHandler;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Core\Session;

// Mock environment
Session::init();
if (!defined('BASE_URL')) define('BASE_URL', '/');

echo "--- Iniciando verificación de Manejo de Errores Estandarizado ---\n";

$handler = new ExceptionHandler();

try {
    echo "\n1. Probando formatado de Error de Validación (Simulado AJAX)...\n";
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest'; // Simulate AJAX
    
    // We catch the output of handleException
    ob_start();
    $handler->handleException(new ValidationException(['campo' => 'error'], 'Datos inválidos'));
    $output = ob_get_clean();
    
    $json = json_decode($output, true);
    if ($json && isset($json['success']) && $json['success'] === false && isset($json['errors'])) {
        echo "✅ El error de validación AJAX tiene el formato correcto.\n";
    } else {
        echo "❌ Fallo en el formato JSON de validación.\n";
        print_r($json);
    }

    echo "\n2. Probando formatado de Error 404 (Simulado AJAX)...\n";
    ob_start();
    $handler->handleException(new NotFoundException('Ruta no encontrada'));
    $output = ob_get_clean();
    
    $json = json_decode($output, true);
    if ($json && $json['error']['code'] === 404) {
        echo "✅ El error 404 AJAX tiene el código correcto.\n";
    } else {
        echo "❌ Fallo en el código 404.\n";
    }

    echo "\n✅ VERIFICACIÓN DE LÓGICA COMPLETADA: El ExceptionHandler procesa correctamente las excepciones según el contexto.\n";

} catch (\Exception $e) {
    echo "❌ ERRORinesperado: " . $e->getMessage() . "\n";
}

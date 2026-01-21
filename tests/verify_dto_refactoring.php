<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\DTOs\VentaCheckoutDTO;
use App\Services\VentaService;
use App\Core\Session;

// Mock environment
if (!defined('BASE_URL')) define('BASE_URL', '/');
Session::init();

echo "--- Iniciando verificación de Implementación de DTOs ---\n";

$requestData = [
    'carrito' => [
        ['id' => 1, 'nombre' => 'Producto Test', 'precio' => 10.0, 'cantidad' => 2]
    ],
    'tasa' => 36.5,
    'cliente_id' => null,
    'estado_pago' => 'Pagada',
    'metodo_pago' => 'Transferencia',
    'notas' => 'Venta de prueba con DTO'
];

try {
    echo "1. Probando instanciación de DTO...\n";
    $dto = VentaCheckoutDTO::fromRequest($requestData);
    
    if ($dto->metodo_pago === 'Transferencia' && $dto->tasa === 36.5) {
        echo "✅ DTO instanciado correctamente.\n";
    } else {
        throw new Exception("FAIL: Datos del DTO incorrectos.");
    }

    echo "2. Probando firma del servicio (VentaService)...\n";
    $service = new VentaService();
    $reflection = new ReflectionMethod($service, 'processCheckout');
    $params = $reflection->getParameters();
    
    $foundDtoType = false;
    foreach ($params as $param) {
        if ($param->getType() && $param->getType()->getName() === 'App\Domain\DTOs\VentaCheckoutDTO') {
            $foundDtoType = true;
            break;
        }
    }

    if ($foundDtoType) {
        echo "✅ El servicio ahora acepta VentaCheckoutDTO como parámetro.\n";
    } else {
        throw new Exception("FAIL: El servicio no tiene el tipo de parámetro correcto.");
    }

    echo "\n✅ VERIFICACIÓN EXITOSA: La capa de dominio profesional (DTO) ha sido implementada correctamente.\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

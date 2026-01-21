<?php
namespace App\Services;

use App\Models\VentaModel;
use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\Cliente;
use App\Models\AuditModel;
use App\Core\Database;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;
use App\Domain\Enums\PaymentStatus;
use App\Domain\Enums\MovementType;
use App\Domain\DTOs\VentaCheckoutDTO;

class VentaService {
    private $ventaModel;
    private $productoModel;
    private $movimientoModel;
    private $clienteModel;
    private $auditModel;
    private $db;

    public function __construct() {
        $this->ventaModel = new VentaModel();
        $this->productoModel = new Producto();
        $this->movimientoModel = new Movimiento();
        $this->clienteModel = new Cliente();
        $this->auditModel = new AuditModel();
        $this->db = Database::conectar();
    }

    public function processCheckout(int $userId, VentaCheckoutDTO $dto) {
        $carrito = $dto->carrito;
        $tasa = $dto->tasa ?? (float)($_SESSION['tasa_bcv'] ?? 1.0);
        $clienteId = $dto->cliente_id;
        $estadoPago = $dto->estado_pago ?? PaymentStatus::PAGADA->value;
        $metodoPago = $dto->metodo_pago;
        $notas = $dto->notas;

        if (empty($carrito)) {
            throw new ValidationException(['carrito' => 'El carrito está vacío.'], 'Error en la venta.');
        }

        // Validate credit sale
        if ($estadoPago === PaymentStatus::PENDIENTE->value) {
            if (!$clienteId) {
                throw new ValidationException(['cliente_id' => 'Debe seleccionar un cliente para vender a crédito.'], 'Error en la venta.');
            }

            $totalTmp = 0;
            foreach ($carrito as $item) {
                $totalTmp += (float)($item['precio'] ?? 0) * (int)$item['cantidad'];
            }

            if (!$this->clienteModel->puedeComprarCredito($clienteId, $totalTmp)) {
                $deudaActual = $this->clienteModel->obtenerDeuda($clienteId);
                $cliente = $this->clienteModel->obtenerPorId($userId, $clienteId);
                throw new AppException('El cliente ha superado su límite de crédito. Límite: $' . number_format($cliente['limite_credito'], 2) . ', Deuda actual: $' . number_format($deudaActual, 2), 400);
            }
        }

        try {
            $this->db->beginTransaction();

            $totalUSD = new Money(0);
            foreach ($carrito as $item) {
                $precio = new Money(isset($item['precio']) ? (float)$item['precio'] : 0);
                $cantidad = new Quantity((int)$item['cantidad']);
                $totalUSD = $totalUSD->add($precio->multiply($cantidad->getValue()));
            }
            $totalVES = $totalUSD->getAmount() * $tasa;

            $ventaId = $this->ventaModel->crearVenta($userId, $totalUSD->getAmount(), $tasa, $totalVES, $clienteId, $estadoPago, $metodoPago, $notas);

            foreach ($carrito as $item) {
                $precio = new Money(isset($item['precio']) ? (float)$item['precio'] : 0);
                $cantidad = new Quantity((int)$item['cantidad']);
                
                // Obtener costo actual para registro histórico
                $producto = $this->productoModel->obtenerPorId($userId, $item['id']);
                $costoUSD = $producto ? (float)$producto['precioCompraUSD'] : 0;
                
                $this->ventaModel->crearVentaItem($ventaId, $item['id'], $item['nombre'], $cantidad->getValue(), $precio->getAmount(), $costoUSD);
                
                // Actualizar Stock usando el modelo refactoreado
                $this->productoModel->actualizarStock($userId, $item['id'], $cantidad->getValue(), MovementType::SALIDA->value);
                
                $this->movimientoModel->crear($userId, $item['id'], $item['nombre'], MovementType::SALIDA->value, 'Venta (POS)', $cantidad->getValue(), 'Venta ID #' . $ventaId, null);
            }

            $this->auditModel->registrar($userId, 'crear', 'venta', $ventaId, "Venta POS #$ventaId", null, [
                'total_usd' => $totalUSD->getAmount(), 'metodo' => $metodoPago, 'estado' => $estadoPago
            ]);

            $this->db->commit();
            return $ventaId;

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Marca una venta como pagada
     */
    public function marcarComoPagada($userId, $ventaId) {
        $venta = $this->ventaModel->obtenerVentaPorId($userId, $ventaId);
        if (!$venta || $venta['estado_pago'] === PaymentStatus::PAGADA->value) {
            return false;
        }

        $exito = $this->ventaModel->marcarPagada($userId, $ventaId);

        if ($exito) {
            $this->auditModel->registrar($userId, 'pagar', 'venta', $ventaId, "Pago de venta #$ventaId", 
                ['estado' => PaymentStatus::PENDIENTE->value], ['estado' => PaymentStatus::PAGADA->value]
            );
            return true;
        }

        return false;
    }
}

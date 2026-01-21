<?php
namespace App\Services;

use App\Models\CompraModel;
use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\AuditModel;
use App\Core\Database;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\Quantity;
use App\Domain\Enums\PaymentStatus;
use App\Domain\Enums\MovementType;

class CompraService {
    private $compraModel;
    private $productoModel;
    private $movimientoModel;
    private $auditModel;
    private $db;

    public function __construct() {
        $this->compraModel = new CompraModel();
        $this->productoModel = new Producto();
        $this->movimientoModel = new Movimiento();
        $this->auditModel = new AuditModel();
        $this->db = Database::conectar();
    }

    public function processPurchase($userId, $data) {
        $carrito = $data['carrito'] ?? [];
        $proveedorId = $data['proveedor_id'] ?? null;
        $nroFactura = $data['nro_factura'] ?? 'S/N';
        $estado = $data['estado'] ?? PaymentStatus::PAGADA->value;
        $fechaEmision = $data['fecha_emision'] ?? date('Y-m-d');
        $fechaVencimiento = $data['fecha_vencimiento'] ?? null;

        if (empty($carrito) || !$proveedorId) {
            throw new \Exception('Datos de compra incompletos.');
        }

        $total = new Money(0);
        foreach ($carrito as $item) {
            $costo = new Money((float)$item['costo']);
            $cantidad = new Quantity((int)$item['cantidad']);
            $total = $total->add($costo->multiply($cantidad->getValue()));
        }

        try {
            $this->db->beginTransaction();

            $compraId = $this->compraModel->crearCompra($userId, $proveedorId, $nroFactura, $total->getAmount(), $estado, $fechaEmision, $fechaVencimiento);

            foreach ($carrito as $item) {
                $costo = new Money((float)$item['costo']);
                $cantidad = new Quantity((int)$item['cantidad']);
                $this->compraModel->crearCompraItem($compraId, $item['id'], $cantidad->getValue(), $costo->getAmount());
                $this->compraModel->actualizarPrecioProveedor($item['id'], $proveedorId, $costo->getAmount());

                if ($estado === PaymentStatus::PAGADA->value) {
                    $this->applyStockAndPrice($userId, $item, $proveedorId, $nroFactura);
                }
            }

            $this->auditModel->registrar($userId, 'crear', 'compra', $compraId, "Compra Factura #$nroFactura", null, [
                'total_usd' => $total->getAmount(), 'proveedor_id' => $proveedorId, 'estado' => $estado
            ]);

            $this->db->commit();
            return $compraId;

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function markAsPaid($userId, $compraId) {
        $compra = $this->compraModel->obtenerPorId($userId, $compraId);
        if (!$compra || $compra['estado'] !== PaymentStatus::PENDIENTE->value) {
            return false;
        }

        try {
            $this->db->beginTransaction();
            $items = $this->compraModel->obtenerItems($compraId);

            foreach ($items as $item) {
                $purchaseItem = [
                    'id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'costo' => $item['precio_unitario'],
                    'nombre' => $item['nombre_producto']
                ];
                $this->applyStockAndPrice($userId, $purchaseItem, $compra['proveedor_id'], $compra['nro_factura'], true);
            }

            $this->compraModel->pagarCompra($compraId);
            
            $this->auditModel->registrar($userId, 'pagar', 'compra', $compraId, "Factura #{$compra['nro_factura']} pagada", 
                ['estado' => PaymentStatus::PENDIENTE->value], ['estado' => PaymentStatus::PAGADA->value]
            );
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function applyStockAndPrice($userId, $item, $proveedorId, $nroFactura, $isDeferred = false) {
        $costo = new Money((float)$item['costo']);
        $cantidad = new Quantity((int)$item['cantidad']);

        $this->productoModel->actualizarStock($userId, $item['id'], $cantidad->getValue(), MovementType::ENTRADA->value);
        
        $prodActual = $this->productoModel->obtenerPorId($userId, $item['id']);
        if ($prodActual) {
            $precioCompraActual = new Money((float)($prodActual['precioCompraUSD'] ?? 0));
            $precioVentaActual = new Money((float)($prodActual['precioVentaUSD'] ?? 0));
            
            $margenPorcentaje = 0;
            if ($precioCompraActual->getAmount() > 0) {
                $margenPorcentaje = (($precioVentaActual->getAmount() / $precioCompraActual->getAmount()) - 1) * 100;
            }

            // CORRECCIÓN: Usar array para la nueva firma de actualizarCompleto
            $this->productoModel->actualizarCompleto($item['id'], [
                'user_id' => $userId,
                'nombre' => $prodActual['nombre'], 
                'precio_base' => $costo->getAmount(), // El Service calculará el resto
                'tiene_iva' => $prodActual['tiene_iva'],
                'iva_porcentaje' => $prodActual['iva_porcentaje'],
                'margen_ganancia' => $margenPorcentaje,
                'proveedor_id' => $proveedorId, 
                'codigo_barras' => $prodActual['codigo_barras']
            ]);
        }

        $tipoMovimiento = $isDeferred ? 'Compra (Pago Diferido)' : 'Compra';
        $nota = "Factura $nroFactura" . ($isDeferred ? " (Diferido)" : "");
        $this->movimientoModel->crear($userId, $item['id'], $item['nombre'] ?? $prodActual['nombre'], MovementType::ENTRADA->value, $tipoMovimiento, $cantidad->getValue(), $nota, null);
    }
}

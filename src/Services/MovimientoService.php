<?php
namespace App\Services;

use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\NotificacionModel;
use App\Core\EmailService;
use App\Core\Database;
use App\Domain\ValueObjects\Quantity;
use App\Domain\Enums\MovementType;

class MovimientoService {
    private $movimientoModel;
    private $productoModel;
    private $proveedorModel;
    private $notifModel;
    private $emailService;
    private $db;

    public function __construct() {
        $this->movimientoModel = new Movimiento();
        $this->productoModel = new Producto();
        $this->proveedorModel = new Proveedor();
        $this->notifModel = new NotificacionModel();
        $this->emailService = new EmailService();
        $this->db = Database::conectar();
    }

    public function registerMovement($userId, $data) {
        $productoId = (int)$data['mov-producto'];
        $tipo = $data['mov-tipo'];
        $motivo = $data['mov-motivo'];
        
        // Uso de Quantity VO
        $cantidad = new Quantity((int)$data['mov-cantidad']);
        
        $proveedorId = (int)$data['mov-proveedor'];
        $nota = trim($data['mov-nota'] ?? '');

        $producto = $this->productoModel->obtenerPorId($userId, $productoId);
        if (!$producto) {
            throw new \Exception('Producto no válido.');
        }

        if ($tipo === MovementType::SALIDA->value && $producto['stock'] < $cantidad->getValue()) {
            throw new \Exception('Stock insuficiente para esta salida.');
        }

        $proveedorNombre = null;
        if ($proveedorId > 0) {
            $proveedor = $this->proveedorModel->obtenerPorId($userId, $proveedorId);
            if ($proveedor) $proveedorNombre = $proveedor['nombre'];
        }

        try {
            $this->db->beginTransaction();

            $this->productoModel->actualizarStock($userId, $productoId, $cantidad->getValue(), $tipo);
            $this->movimientoModel->crear(
                $userId, $productoId, $producto['nombre'], $tipo,
                $motivo, $cantidad->getValue(), $nota, $proveedorNombre
            );

            $this->db->commit();

            // Handle side effects (Alerts & Emails)
            if ($tipo === MovementType::SALIDA->value) {
                $this->checkStockAlerts($userId, $productoId);
            }

            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function checkStockAlerts($userId, $productoId) {
        $productoActualizado = $this->productoModel->obtenerPorId($userId, $productoId);
        $umbral = $_SESSION['stock_umbral'] ?? 10;

        if ($productoActualizado && $productoActualizado['stock'] <= $umbral) {
            $this->notifModel->crearAlertaStock($userId, $productoActualizado['nombre'], 
                $productoActualizado['stock'], $umbral);
            
            if ($productoActualizado['stock'] == 0 && $this->emailService->estaConfigurado()) {
                $this->sendOutOfStockEmail($userId, $productoActualizado);
            }
        }
    }

    private function sendOutOfStockEmail($userId, $producto) {
        try {
            $stmt = $this->db->prepare("SELECT u.email, c.nombre_negocio FROM usuarios u 
                LEFT JOIN configuracion c ON u.id = c.user_id WHERE u.id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch();
            
            if ($userData && $userData['email']) {
                $this->emailService->enviarAlertaStock(
                    $userData['email'],
                    $userData['nombre_negocio'] ?? 'SaaS Pro',
                    [['nombre' => $producto['nombre'], 'stock' => 0]]
                );
            }
        } catch (\Exception $e) {
            error_log("Error in MovimientoService email: " . $e->getMessage());
        }
    }
}

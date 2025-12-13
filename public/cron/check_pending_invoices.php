<?php
/**
 * Script Cron: Verificar facturas por vencer
 * Ejecutar diariamente: php check_pending_invoices.php
 * 
 * En Windows (Task Scheduler) o Linux (crontab):
 * 0 8 * * * php /path/to/check_pending_invoices.php
 */

// Cargar el autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;
use App\Core\EmailService;
use App\Models\NotificacionModel;

// Iniciar sesión simulada para el script
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "=== Verificando facturas por vencer ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = Database::conectar();
    
    // Obtener todas las compras pendientes que vencen en los próximos 7 días
    $query = "SELECT c.*, 
                     p.nombre as proveedor,
                     u.email as user_email,
                     cfg.nombre_negocio,
                     DATEDIFF(c.fecha_vencimiento, CURDATE()) as dias_restantes
              FROM compras c
              INNER JOIN proveedores p ON c.proveedor_id = p.id
              INNER JOIN usuarios u ON c.user_id = u.id
              LEFT JOIN configuracion cfg ON c.user_id = cfg.user_id
              WHERE c.estado = 'Pendiente' 
                AND c.fecha_vencimiento IS NOT NULL
                AND c.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
              ORDER BY c.fecha_vencimiento ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $facturas = $stmt->fetchAll();
    
    echo "Facturas encontradas: " . count($facturas) . "\n\n";
    
    $notifModel = new NotificacionModel();
    $emailService = new EmailService();
    $emailConfigurado = $emailService->estaConfigurado();
    
    foreach ($facturas as $factura) {
        $_SESSION['user_id'] = $factura['user_id']; // Simular sesión del usuario
        
        $diasRestantes = (int)$factura['dias_restantes'];
        
        echo "- Factura #{$factura['nro_factura']} de {$factura['proveedor']}: ";
        echo "vence en $diasRestantes día(s)\n";
        
        // Crear notificación en el sistema
        $notifModel->crearAlertaFactura(
            $factura['user_id'],
            $factura['proveedor'],
            $factura['total_usd'],
            $diasRestantes
        );
        
        // Enviar email si está configurado y es urgente (3 días o menos)
        if ($emailConfigurado && $diasRestantes <= 3) {
            $resultado = $emailService->enviarRecordatorioFactura(
                $factura['user_email'],
                $factura['nombre_negocio'] ?? 'SaaS Pro',
                [
                    'proveedor' => $factura['proveedor'],
                    'nro_factura' => $factura['nro_factura'],
                    'total_usd' => number_format($factura['total_usd'], 2),
                    'fecha_vencimiento' => date('d/m/Y', strtotime($factura['fecha_vencimiento'])),
                    'dias_restantes' => $diasRestantes
                ]
            );
            
            if ($resultado['success']) {
                echo "  ✓ Email enviado a {$factura['user_email']}\n";
            } else {
                echo "  ✗ Error email: {$resultado['message']}\n";
            }
        }
    }
    
    echo "\n=== Proceso completado ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

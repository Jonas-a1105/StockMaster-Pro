<?php
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Servicio de Email - Envío de notificaciones y alertas por correo
 */
class EmailService {
    
    private $mailer;
    private $configurado = false;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configurar();
    }
    
    /**
     * Configurar el servidor SMTP
     * Los valores se pueden cambiar en Negocio > Configuración
     */
    private function configurar() {
        try {
            // Obtener configuración de BD o usar valores por defecto
            $config = $this->obtenerConfiguracion();
            
            if (empty($config['smtp_host']) || empty($config['smtp_user'])) {
                $this->configurado = false;
                return;
            }
            
            $this->mailer->isSMTP();
            $this->mailer->Host = $config['smtp_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $config['smtp_user'];
            $this->mailer->Password = $config['smtp_pass'];
            $this->mailer->SMTPSecure = $config['smtp_secure'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $config['smtp_port'] ?? 587;
            
            $this->mailer->setFrom($config['smtp_user'], $config['nombre_negocio'] ?? 'SaaS Pro');
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
            $this->configurado = true;
        } catch (Exception $e) {
            $this->configurado = false;
            error_log("Error configurando email: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener configuración de email desde la BD
     */
    private function obtenerConfiguracion() {
        try {
            $db = Database::conectar();
            $userId = $_SESSION['user_id'] ?? 0;
            
            $stmt = $db->prepare("SELECT * FROM configuracion WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $config = $stmt->fetch();
            
            return $config ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Verificar si el servicio está configurado
     */
    public function estaConfigurado() {
        return $this->configurado;
    }
    
    /**
     * Enviar email genérico
     */
    public function enviar($destinatario, $asunto, $cuerpoHtml, $cuerpoTexto = '') {
        if (!$this->configurado) {
            return ['success' => false, 'message' => 'Email no configurado. Configure SMTP en Negocio > Configuración.'];
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($destinatario);
            $this->mailer->Subject = $asunto;
            $this->mailer->Body = $cuerpoHtml;
            $this->mailer->AltBody = $cuerpoTexto ?: strip_tags($cuerpoHtml);
            
            $this->mailer->send();
            return ['success' => true, 'message' => 'Email enviado correctamente'];
        } catch (Exception $e) {
            error_log("Error enviando email: " . $this->mailer->ErrorInfo);
            return ['success' => false, 'message' => 'Error: ' . $this->mailer->ErrorInfo];
        }
    }
    
    /**
     * Enviar alerta de stock bajo/agotado
     */
    public function enviarAlertaStock($destinatario, $nombreNegocio, $productos) {
        if (empty($productos)) return ['success' => false, 'message' => 'No hay productos'];
        
        $asunto = "⚠️ Alerta de Stock - $nombreNegocio";
        
        $html = $this->plantillaEmail("
            <h2 style='color: #dc3545;'>⚠️ Alerta de Inventario</h2>
            <p>Los siguientes productos requieren atención:</p>
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                <thead>
                    <tr style='background: #f8f9fa;'>
                        <th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Producto</th>
                        <th style='padding: 10px; border: 1px solid #ddd; text-align: center;'>Stock</th>
                        <th style='padding: 10px; border: 1px solid #ddd; text-align: center;'>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    " . $this->generarFilasProductos($productos) . "
                </tbody>
            </table>
            <p style='margin-top: 20px;'>
                <a href='" . $this->getBaseUrl() . "?controlador=producto' 
                   style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                   Ver Inventario
                </a>
            </p>
        ", $nombreNegocio);
        
        return $this->enviar($destinatario, $asunto, $html);
    }
    
    /**
     * Enviar recordatorio de factura por vencer
     */
    public function enviarRecordatorioFactura($destinatario, $nombreNegocio, $factura) {
        $diasRestantes = $factura['dias_restantes'] ?? 0;
        $urgencia = $diasRestantes <= 1 ? '🔴 URGENTE' : ($diasRestantes <= 3 ? '🟡 Próxima' : '🔵 Recordatorio');
        
        $asunto = "$urgencia: Factura por vencer - $nombreNegocio";
        
        $html = $this->plantillaEmail("
            <h2 style='color: " . ($diasRestantes <= 1 ? '#dc3545' : '#ffc107') . ";'>
                $urgencia - Factura por Vencer
            </h2>
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>
                <p><strong>Proveedor:</strong> {$factura['proveedor']}</p>
                <p><strong>Nº Factura:</strong> {$factura['nro_factura']}</p>
                <p><strong>Monto:</strong> \${$factura['total_usd']}</p>
                <p><strong>Vence:</strong> {$factura['fecha_vencimiento']}</p>
                <p style='font-size: 1.2em; font-weight: bold; color: " . ($diasRestantes <= 1 ? '#dc3545' : '#ffc107') . ";'>
                    " . ($diasRestantes == 0 ? '¡Vence HOY!' : ($diasRestantes == 1 ? 'Vence MAÑANA' : "Vence en $diasRestantes días")) . "
                </p>
            </div>
            <p>
                <a href='" . $this->getBaseUrl() . "?controlador=compra' 
                   style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                   Ver Compras Pendientes
                </a>
            </p>
        ", $nombreNegocio);
        
        return $this->enviar($destinatario, $asunto, $html);
    }
    
    /**
     * Plantilla base para emails
     */
    private function plantillaEmail($contenido, $nombreNegocio = 'SaaS Pro') {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0;'>$nombreNegocio</h1>
            </div>
            <div style='background: white; padding: 30px; border: 1px solid #ddd; border-top: none;'>
                $contenido
            </div>
            <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 0.85em; color: #666; border-radius: 0 0 10px 10px;'>
                <p style='margin: 0;'>Este es un mensaje automático de SaaS Pro</p>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Generar filas de productos para tabla HTML
     */
    private function generarFilasProductos($productos) {
        $html = '';
        foreach ($productos as $p) {
            $estado = $p['stock'] == 0 ? 
                "<span style='color: #dc3545; font-weight: bold;'>AGOTADO</span>" : 
                "<span style='color: #ffc107; font-weight: bold;'>Stock Bajo</span>";
            $bgColor = $p['stock'] == 0 ? '#ffebee' : '#fff8e1';
            
            $html .= "
                <tr style='background: $bgColor;'>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$p['nombre']}</td>
                    <td style='padding: 10px; border: 1px solid #ddd; text-align: center;'>{$p['stock']}</td>
                    <td style='padding: 10px; border: 1px solid #ddd; text-align: center;'>$estado</td>
                </tr>";
        }
        return $html;
    }
    
    /**
     * Obtener URL base del sistema
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
        return "$protocol://$host$path/index.php";
    }
}

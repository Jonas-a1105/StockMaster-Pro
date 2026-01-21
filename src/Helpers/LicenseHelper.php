<?php
namespace App\Helpers;

use App\Core\Database;

class LicenseHelper {
    // La clave secreta ahora se obtiene desde el archivo .env (LICENSE_SECRET_KEY)

    /**
     * Valida y activa una licencia.
     * Retorna array ['success' => bool, 'message' => string]
     */
    public static function activarLicencia($licenseKey) {
        $parts = explode('.', $licenseKey);
        
        if (count($parts) !== 2) {
            return ['success' => false, 'message' => 'Formato de licencia inválido.'];
        }

        [$payloadBase64, $signature] = $parts;
        
        // 1. Verificar Firma
        $secretKey = getenv('LICENSE_SECRET_KEY') ?: 'ENTERPRISE_SECRET_KEY_v2025_SECURE';
        $calculatedSignature = hash_hmac('sha256', $payloadBase64, $secretKey);
        
        if (!hash_equals($calculatedSignature, $signature)) {
            return ['success' => false, 'message' => 'Licencia inválida o alterada.'];
        }

        // 2. Decodificar Payload
        $payloadJson = base64_decode($payloadBase64);
        $payload = json_decode($payloadJson, true);

        if (!$payload || !isset($payload['dias'], $payload['creado'])) {
            return ['success' => false, 'message' => 'Datos de licencia corruptos.'];
        }

        // 3. Activar en Base de Datos
        $dias = (int)$payload['dias'];
        $unidad = $payload['unidad'] ?? 'days'; // Soporte para 'days' o 'minutes'
        
        $fechaActivacion = new \DateTime();
        // Construir string de modificación: "+30 days", "+1 minutes", etc.
        $fechaVencimiento = (new \DateTime())->modify("+$dias $unidad");

        try {
            $db = Database::conectar();

            // ⚠️ VALIDACIÓN ANTI-REUSO: Verificar si la clave ya fue usada antes
            $stmtCheck = $db->prepare("SELECT id FROM sistema_licencias WHERE license_key = ? LIMIT 1");
            $stmtCheck->execute([$licenseKey]);
            if ($stmtCheck->fetch()) {
                return ['success' => false, 'message' => 'Esta licencia ya ha sido utilizada anteriormente.'];
            }
            
            // Invalidar licencias anteriores
            $db->exec("UPDATE sistema_licencias SET status = 'replaced' WHERE status = 'active'");

            $stmt = $db->prepare("INSERT INTO sistema_licencias (license_key, activation_date, expiration_date, status, signature_hash) VALUES (?, ?, ?, 'active', ?)");
            $stmt->execute([
                $licenseKey,
                $fechaActivacion->format('Y-m-d H:i:s'),
                $fechaVencimiento->format('Y-m-d H:i:s'),
                $signature
            ]);

            return ['success' => true, 'message' => "Licencia activada por $dias días."];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Verifica si el sistema tiene una licencia activa.
     * Retorna bool
     */
    public static function validarEstado() {
        try {
            $db = Database::conectar();
            $stmt = $db->query("SELECT * FROM sistema_licencias WHERE status = 'active' ORDER BY id DESC LIMIT 1");
            $licencia = $stmt->fetch();

            if (!$licencia) {
                return false; // No hay licencia
            }

            $expiracion = new \DateTime($licencia['expiration_date']);
            $hoy = new \DateTime();

            if ($hoy > $expiracion) {
                // Marcar como expirada si ya pasó la fecha
                $update = $db->prepare("UPDATE sistema_licencias SET status = 'expired' WHERE id = ?");
                $update->execute([$licencia['id']]);
                return false;
            }

            return true; // Licencia válida y activa

        } catch (\Exception $e) {
            return false; // Error seguro (bloquear si falla BD)
        }
    }

    /**
     * Obtiene info de la licencia actual (días restantes, etc)
     */
    public static function obtenerInfoLicencia() {
        try {
            $db = Database::conectar();
            $stmt = $db->query("SELECT * FROM sistema_licencias WHERE status = 'active' ORDER BY id DESC LIMIT 1");
            $licencia = $stmt->fetch();

            if (!$licencia) return null;

            $exp = new \DateTime($licencia['expiration_date']);
            $hoy = new \DateTime();
            $diasRestantes = $hoy->diff($exp)->days;

            // Si ya venció (por horas), asegurar days es 0
            if ($hoy > $exp) $diasRestantes = 0;

            return [
                'expiration_date' => $exp->format('d/m/Y'),
                'days_remaining' => $diasRestantes,
                'key_masked' => substr($licencia['license_key'], 0, 10) . '...'
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}

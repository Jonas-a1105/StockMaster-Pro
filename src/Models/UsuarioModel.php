<?php
namespace App\Models;

use App\Core\Database;
use \PDO;

class UsuarioModel {
    
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    // --- Funciones de Admin ---
    public function obtenerTodos() {
        // Obtiene todos menos el usuario actual (para no borrarse a sí mismo por error)
        $query = "SELECT id, email, plan, rol, created_at as fecha_registro, trial_ends_at FROM usuarios WHERE id != ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$_SESSION['user_id'] ?? 0]);
        return $stmt->fetchAll();
    }

   // --- FUNCIÓN ACTUALIZADA ---
    /**
     * Actualiza el plan y la fecha de vencimiento
     * @param int $usuarioId
     * @param string $nuevoPlan ('free' o 'premium')
     * @param string|null $fechaVencimiento Fecha Y-m-d H:i:s o NULL para permanente
     */
    public function actualizarPlan($usuarioId, $nuevoPlan, $fechaVencimiento = null) {
        // Si lo bajamos a free, la fecha siempre es NULL (sin trial)
        if ($nuevoPlan === 'free') {
            $fechaVencimiento = null;
        }
        
        $query = "UPDATE usuarios SET plan = ?, trial_ends_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$nuevoPlan, $fechaVencimiento, $usuarioId]);
    }

    // --- Funciones de Perfil (Cambio de datos) ---
    public function getPasswordById($userId) {
        $stmt = $this->db->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? $result['password'] : null;
    }

    public function updatePasswordById($userId, $newPasswordHash) {
        $stmt = $this->db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        return $stmt->execute([$newPasswordHash, $userId]);
    }

    public function updateEmail($userId, $newEmail) {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET email = ? WHERE id = ?");
            return $stmt->execute([$newEmail, $userId]);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { return false; } // Email duplicado
            throw $e;
        }
    }

    public function updateUsername($userId, $newUsername) {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET username = ? WHERE id = ?");
            return $stmt->execute([$newUsername, $userId]);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { return false; } // Username duplicado
            throw $e;
        }
    }

    // --- Funciones de Recuperación de Contraseña (Password Reset) ---
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function createResetToken($email, $token) {
        $this->db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
        // Compatibilidad SQLite: Calcular fecha en PHP
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $query = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$email, $token, $expires]);
    }

    public function getResetToken($token) {
        // Compatibilidad SQLite: Usar fecha actual de PHP
        $now = date('Y-m-d H:i:s');
        $query = "SELECT * FROM password_resets WHERE token = ? AND expires_at > ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$token, $now]);
        return $stmt->fetch();
    }

    public function updatePassword($email, $newPasswordHash) {
        $query = "UPDATE usuarios SET password = ? WHERE email = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$newPasswordHash, $email]);
    }

    public function deleteResetToken($token) {
        $query = "DELETE FROM password_resets WHERE token = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$token]);
    }
        // En UsuarioModel.php
    public function limpiarTokensVencidos() {
        // Borrar tokens creados hace más de 24 horas
        $cutoff = date('Y-m-d H:i:s', strtotime('-1 day'));
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE expires_at < ?");
        $stmt->execute([$cutoff]);
}
    // --- REMEMBER ME (Auto Login) ---
    
    public function setRememberToken($userId, $token) {
        $hash = hash('sha256', $token);
        $stmt = $this->db->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
        return $stmt->execute([$hash, $userId]);
    }

    public function findByRememberToken($token) {
        $hash = hash('sha256', $token);
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE remember_token = ?");
        $stmt->execute([$hash]);
        $res = $stmt->fetch();
        return $res;
    }

    public function removeRememberToken($userId) {
        $stmt = $this->db->prepare("UPDATE usuarios SET remember_token = NULL WHERE id = ?");
        return $stmt->execute([$userId]);
    }
}
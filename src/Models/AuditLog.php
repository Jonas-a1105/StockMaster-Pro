<?php
namespace App\Models;

use App\Core\Database;

class AuditLog {
    
    public static function log($userId, $action, $details = '') {
        $db = Database::conectar();
        $query = "INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId, $action, $details]);
    }
}

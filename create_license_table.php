<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

try {
    echo "Conectando a la Base de Datos...\n";
    $db = Database::conectar();

    // Detectar el driver (mysql o sqlite)
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "Driver detectado: " . $driver . "\n";

    if ($driver === 'sqlite') {
        // Sintaxis SQLite
        $sql = "CREATE TABLE IF NOT EXISTS sistema_licencias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_key TEXT NOT NULL,
            activation_date DATETIME NOT NULL,
            expiration_date DATETIME NOT NULL,
            status TEXT DEFAULT 'active',
            signature_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );";
    } else {
        // Sintaxis MySQL (Default)
        $sql = "CREATE TABLE IF NOT EXISTS sistema_licencias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            license_key VARCHAR(255) NOT NULL,
            activation_date DATETIME NOT NULL,
            expiration_date DATETIME NOT NULL,
            status VARCHAR(20) DEFAULT 'active',
            signature_hash VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }

    $db->exec($sql);
    echo "Tabla 'sistema_licencias' verificada/creada exitosamente ($driver).\n";

} catch (PDOException $e) {
    echo "Error BD: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error General: " . $e->getMessage() . "\n";
}

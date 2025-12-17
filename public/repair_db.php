<?php
// Force SQLite for repair
putenv('DB_CONNECTION=sqlite');
require_once __DIR__ . '/../src/Core/Database.php';

try {
    echo "Conectando a base de datos...\n";
    $db = \App\Core\Database::conectar();
    
    echo "Creando tabla sistema_licencias...\n";
    $sql = "CREATE TABLE IF NOT EXISTS sistema_licencias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        license_key TEXT NOT NULL,
        activation_date DATETIME NOT NULL,
        expiration_date DATETIME NOT NULL,
        status TEXT DEFAULT 'active',
        signature_hash TEXT
    )";
    
    $db->exec($sql);
    echo "Tabla sistema_licencias creada (o ya existía).\n";
    echo "Listo. Intente activar la licencia nuevamente.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

<?php
// Force SQLite
putenv('DB_CONNECTION=sqlite');
require_once __DIR__ . '/../src/Core/Database.php';

try {
    echo "Iniciando sistema de actualización de BD...\n";
    $db = \App\Core\Database::conectar();
    
    // ---------------------------------------------------------
    // 1. Asegurar tabla de control de migraciones
    // ---------------------------------------------------------
    $db->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        migration VARCHAR(255) NOT NULL,
        batch INTEGER NOT NULL DEFAULT 1,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // ---------------------------------------------------------
    // 2. Esquema Base (Siempre seguro correrlo porque usa IF NOT EXISTS)
    // ---------------------------------------------------------
    $schemaFile = __DIR__ . '/../migrations/sqlite_schema.sql';
    if (file_exists($schemaFile)) {
        echo "Verificando esquema base...\n";
        $sqlContent = file_get_contents($schemaFile);
        $db->exec($sqlContent);
    }

    // ---------------------------------------------------------
    // 3. Ejecutar Migraciones Incrementales
    // ---------------------------------------------------------
    $versionsDir = __DIR__ . '/../migrations/versions';
    
    // Obtener migraciones ya aplicadas
    $stmt = $db->query("SELECT migration FROM migrations");
    $applied = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Escanear carpeta de versiones
    if (is_dir($versionsDir)) {
        $files = scandir($versionsDir);
        $migrationFiles = [];
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $migrationFiles[] = $file;
            }
        }
        
        // Ordenar alfabéticamente para asegurar orden de ejecución
        sort($migrationFiles);
        
        foreach ($migrationFiles as $file) {
            if (!in_array($file, $applied)) {
                echo "Ejecutando migración: $file ... ";
                $filePath = $versionsDir . '/' . $file;
                $sql = file_get_contents($filePath);
                
                try {
                    $db->beginTransaction();
                    $db->exec($sql);
                    
                    // Registrar migración
                    $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                    $stmt->execute([$file, 1]);
                    
                    $db->commit();
                    echo "✅ HECHO.\n";
                } catch (Exception $e) {
                    $db->rollBack();
                    echo "❌ ERROR.\n";
                    throw $e; // Detener todo si falla una migración
                }
            }
        }
    }

    // ---------------------------------------------------------
    // 4. Verificaciones Finales (Licencias, etc)
    // ---------------------------------------------------------
    echo "Verificando modulo de licencias...\n";
    $sqlLic = "CREATE TABLE IF NOT EXISTS sistema_licencias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        license_key TEXT NOT NULL,
        activation_date DATETIME NOT NULL,
        expiration_date DATETIME NOT NULL,
        status TEXT DEFAULT 'active',
        signature_hash TEXT
    )";
    $db->exec($sqlLic);
    
    echo "----------------------------------------\n";
    echo "✅ Base de datos sincronizada correctamente.\n";
    echo "----------------------------------------\n";

} catch (Exception $e) {
    echo "❌ Error crítico ejecutando migraciones: " . $e->getMessage() . "\n";
    exit(1);
}


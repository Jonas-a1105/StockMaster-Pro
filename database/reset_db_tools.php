<?php
$dbPath = __DIR__ . '/database.sqlite';
$backupPath = __DIR__ . '/database.sqlite.bak';
$schemaPath = dirname(__DIR__) . '/migrations/sqlite_schema.sql';

// 1. Backup existing DB (overwrite previous backup)
if (file_exists($dbPath)) {
    if (file_exists($backupPath)) {
        unlink($backupPath); 
    }
    rename($dbPath, $backupPath);
    echo "Backed up existing database to " . basename($backupPath) . "\n";
}

// 2. Create new DB
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 3. Read and Execute Schema
    if (!file_exists($schemaPath)) {
        die("Error: Schema file not found at $schemaPath\n");
    }
    
    $sql = file_get_contents($schemaPath);
    
    // Execute multiple queries
    $pdo->exec($sql);
    
    echo "Created new clean database at " . basename($dbPath) . " with schema imported.\n";
    
    // Verification logic inline
    $stmt = $pdo->query("PRAGMA table_info(productos)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    if (in_array('codigo', $columns)) {
        echo "VERIFICATION SUCCESS: 'codigo' column exists in productos.\n";
    } else {
        echo "VERIFICATION FAILED: 'codigo' column missing in productos.\n";
    }

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}

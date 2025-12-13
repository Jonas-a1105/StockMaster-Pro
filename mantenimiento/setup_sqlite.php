<?php
// setup_sqlite.php
// Initializes the SQLite database from schema

// Path setup
$dbFile = __DIR__ . '/database/database.sqlite';
$schemaFile = __DIR__ . '/migrations/sqlite_schema.sql';

// Check if DB exists
if (file_exists($dbFile)) {
    echo "Database file already exists at: $dbFile\n";
    // echo "Deleting for fresh start...\n";
    // unlink($dbFile);
}

// Create Directory
if (!is_dir(dirname($dbFile))) {
    mkdir(dirname($dbFile), 0777, true);
}

try {
    // Connect
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to SQLite.\n";

    // Read Schema
    $sql = file_get_contents($schemaFile);
    
    // Execute Schema (SQLite can handle multiple statements in exec usually, or we split)
    if ($sql) {
        $pdo->exec($sql);
        echo "Schema applied successfully.\n";
    }

    // Check if user exists
    $stmt = $pdo->query("SELECT count(*) FROM usuarios");
    if ($stmt->fetchColumn() == 0) {
        // Create default user
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        
        // Insert admin user
        $pdo->exec("INSERT INTO usuarios (email, password, empresa_nombre, rol, plan, username) VALUES ('admin@admin.com', '$pass', 'Mi Empresa', 'admin', 'premium', 'admin')");
        echo "Default user created: admin@admin.com / admin123\n";
    } else {
        echo "Users already exist. Skipping seed.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

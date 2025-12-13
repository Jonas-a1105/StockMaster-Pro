<?php
// inspect_products_schema.php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

// Explicitly set errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Checking SQLite schema for 'productos'...\n";

// FORCE SQLITE
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=' . __DIR__ . '/database/database.sqlite');

try {
    $db = Database::conectar();
    echo "Connected to SQLite.\n";

    $stmt = $db->query("PRAGMA table_info(productos)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Columns in 'productos':\n";
    $hasCodigo = false;
    foreach ($columns as $col) {
        echo "- " . $col['name'] . " (" . $col['type'] . ")\n";
        if ($col['name'] === 'codigo') {
            $hasCodigo = true;
        }
    }

    if (!$hasCodigo) {
        echo "\nACTION REQUIRED: Column 'codigo' is MISSING.\n";
    } else {
        echo "\nOK: Column 'codigo' exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

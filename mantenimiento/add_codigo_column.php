<?php
// add_codigo_column.php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

// Explicitly set errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Checking database connection...\n";

// FORCE SQLITE
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=' . __DIR__ . '/database/database.sqlite');

try {
    $db = Database::conectar();
    echo "Connected to SQLite: " . getenv('DB_DATABASE') . "\n";

    // Check if column exists
    try {
        $db->query("SELECT codigo FROM productos LIMIT 1");
        echo "Column 'codigo' already exists within 'productos'.\n";
    } catch (PDOException $e) {
        echo "Column 'codigo' not found. Adding it...\n";
        
        // SQLite: ALTER TABLE table_name ADD COLUMN column_name column_type;
        $sql = "ALTER TABLE productos ADD COLUMN codigo VARCHAR(50) NULL";
        
        $db->exec($sql);
        echo "Column 'codigo' added successfully.\n";
        
        // Optional: Backfill 'codigo' with 'codigo_barras' or generated ID for existing rows
        echo "Backfilling empty 'codigo' values...\n";
        $update = "UPDATE productos SET codigo = codigo_barras WHERE codigo IS NULL OR codigo = ''";
        $db->exec($update);
        
        // For rows where codigo_barras was also null, generate a PROD-ID
        // SQLite doesn't have easy procedural loop here, but we can do a simple update if needed.
        // For now, simple backfill is enough.
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

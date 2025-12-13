<?php
// add_remember_token_column.php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

// Explicitly set errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Checking database connection...\n";

// FORCE SQLITE for this script to match Desktop App behavior
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=' . __DIR__ . '/database/database.sqlite');

try {
    $db = Database::conectar();
    echo "Connected to SQLite: " . getenv('DB_DATABASE') . "\n";

    // Check if column exists (SQLite specific check first, then generic)
    // For SQLite, 'PRAGMA table_info(usuarios)'
    // For MySQL, 'DESCRIBE usuarios'
    
    // We'll try to just add it and catch the error if it exists, or check first.
    // Simpler: Try to select it.
    try {
        $db->query("SELECT remember_token FROM usuarios LIMIT 1");
        echo "Column 'remember_token' already exists.\n";
    } catch (PDOException $e) {
        echo "Column 'remember_token' not found. Adding it...\n";
        
        // SQLite and MySQL compatible-ish for adding column (MySQL allows AFTER, SQLite doesn't)
        // SQLite: ALTER TABLE table_name ADD COLUMN column_name column_type;
        $sql = "ALTER TABLE usuarios ADD COLUMN remember_token VARCHAR(255) NULL";
        
        $db->exec($sql);
        echo "Column 'remember_token' added successfully.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

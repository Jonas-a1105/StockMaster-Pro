<?php
namespace App\Core;

use \PDO;
use \PDOException;

class Database {
    
    // --- ¡CAMBIO AQUI! ---
    // Ya no definimos las credenciales aquí.
    // Las leeremos de variables de entorno.
    private static $conn;

    public static function conectar() {
        if (self::$conn === null) {
            
            // Configuration
            $driver = getenv('DB_CONNECTION') ?: 'mysql'; // switchable: mysql | sqlite
            
            try {
                if ($driver === 'sqlite') {
                    // Path to sqlite file (default: database/database.sqlite in project root)
                    // We assume the DB is in the root or a 'database' folder relative to this file
                    // __DIR__ is src/Core. Project root is __DIR__/../../
                    $dbPath = getenv('DB_DATABASE') ?: __DIR__ . '/../../database/database.sqlite';
                    
                    // Create directory if it doesn't exist (basic check)
                    $dir = dirname($dbPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }

                    $dsn = 'sqlite:' . $dbPath;
                    self::$conn = new PDO($dsn);
                } else {
                    // MySQL / MariaDB (Default XAMPP)
                    $host = getenv('DB_HOST') ?: '127.0.0.1';
                    $db_name = getenv('DB_NAME') ?: 'inventario_oop';
                    $username = getenv('DB_USER') ?: 'root';
                    $password = getenv('DB_PASS') ?: '';
                    
                    $dsn = 'mysql:host=' . $host . ';dbname=' . $db_name . ';charset=utf8mb4';
                    self::$conn = new PDO($dsn, $username, $password);
                }

                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // Enable foreign keys for SQLite
                if ($driver === 'sqlite') {
                    self::$conn->exec('PRAGMA foreign_keys = ON;');
                }

            } catch (PDOException $e) {
                error_log('Error de conexión a BBDD (' . $driver . '): ' . $e->getMessage());
                // In production/desktop logic, we might want to handle this gracefully
                throw new \PDOException('Error de conexión a la Base de Datos. Por favor, verifique la configuración.', 0, $e);
            }
        }
        return self::$conn;
    }
}
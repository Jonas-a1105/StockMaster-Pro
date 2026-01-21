<?php
namespace App\Core\Migration;

use App\Core\Database;
use App\Core\Logger;
use Exception;

class MigrationManager {
    private $db;
    private $migrationsPath;

    public function __construct() {
        $this->db = Database::conectar();
        $this->migrationsPath = __DIR__ . '/../../../database/migrations';
        
        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0777, true);
        }
    }

    public function run() {
        $this->ensureMigrationsTable();
        
        $executed = $this->getExecutedMigrations();
        $files = scandir($this->migrationsPath);
        
        $count = 0;
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || !str_ends_with($file, '.sql')) {
                continue;
            }
            
            if (!in_array($file, $executed)) {
                $this->executeMigration($file);
                $count++;
            }
        }
        
        return $count;
    }

    private function ensureMigrationsTable() {
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);
        
        if ($driver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        }
        
        $this->db->exec($sql);
    }

    private function getExecutedMigrations() {
        $stmt = $this->db->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function executeMigration($file) {
        $path = $this->migrationsPath . '/' . $file;
        $sql = file_get_contents($path);
        
        if (empty($sql)) return;

        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        try {
            $this->db->beginTransaction();
            
            // PDO exec only handles one statement. For multiple, we might need to split or use a loop.
            // Simplified for now: assuming each .sql is a set of semicolon-separated statements.
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    // SQLite specific skip: FULLTEXT INDEX is not supported
                    if ($driver === 'sqlite' && stripos($stmt, 'FULLTEXT INDEX') !== false) {
                        Logger::info("Skipping MySQL-specific statement for SQLite: $stmt");
                        continue;
                    }
                    $this->db->exec($stmt);
                }
            }
            
            $stmt = $this->db->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$file]);
            
            $this->db->commit();
            Logger::info("Migration executed: $file");
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error("Migration failed: $file - " . $e->getMessage());
            throw $e;
        }
    }
}

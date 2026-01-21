<?php
/**
 * CLI Migration Runner for StockMaster-Pro
 * Usage: php bin/migrate.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Migration\MigrationManager;
use App\Core\Logger;

echo "--- StockMaster-Pro Migration Runner ---\n";

try {
    $manager = new MigrationManager();
    $count = $manager->run();
    
    if ($count > 0) {
        echo "ÉXITO: Se ejecutaron $count migraciones nuevas.\n";
    } else {
        echo "El sistema está al día. No hay migraciones pendientes.\n";
    }
} catch (Exception $e) {
    echo "ERROR FATAL: " . $e->getMessage() . "\n";
    Logger::error("CLI Migration error: " . $e->getMessage());
    exit(1);
}

exit(0);

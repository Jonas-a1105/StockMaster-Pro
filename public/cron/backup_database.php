<?php
/**
 * Script de Backup Automático
 * Ejecutar como cron job diario: php backup_database.php
 * 
 * Windows Task Scheduler: php C:\xampp\htdocs\inventario_oop\public\cron\backup_database.php
 */

// Configuración
$config = [
    'mysql_path' => 'C:\\xampp\\mysql\\bin\\mysqldump.exe',
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '', // Cambiar si tienes contraseña
    'database' => 'inventario_oop',
    'backup_dir' => __DIR__ . '/../../storage/backups',
    'max_backups' => 7 // Mantener solo los últimos 7 backups
];

echo "=== Backup Automático ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Crear directorio si no existe
if (!is_dir($config['backup_dir'])) {
    mkdir($config['backup_dir'], 0755, true);
}

// Nombre del archivo de backup
$backupFile = $config['backup_dir'] . '/backup_' . date('Y-m-d_His') . '.sql';

// Construir comando mysqldump
$cmd = sprintf(
    '"%s" --host=%s --user=%s %s %s > "%s"',
    $config['mysql_path'],
    $config['host'],
    $config['user'],
    $config['pass'] ? '--password=' . $config['pass'] : '',
    $config['database'],
    $backupFile
);

echo "Ejecutando backup...\n";
exec($cmd, $output, $returnCode);

if ($returnCode === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
    // Comprimir el backup
    $zipFile = $backupFile . '.zip';
    $zip = new ZipArchive();
    
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        $zip->addFile($backupFile, basename($backupFile));
        $zip->close();
        unlink($backupFile); // Eliminar archivo SQL sin comprimir
        
        $size = round(filesize($zipFile) / 1024, 2);
        echo "✓ Backup creado: " . basename($zipFile) . " ({$size} KB)\n";
    } else {
        echo "✓ Backup creado (sin comprimir): " . basename($backupFile) . "\n";
    }
    
    // Limpiar backups antiguos
    $backups = glob($config['backup_dir'] . '/backup_*.zip');
    usort($backups, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    $deleted = 0;
    foreach (array_slice($backups, $config['max_backups']) as $oldBackup) {
        unlink($oldBackup);
        $deleted++;
    }
    
    if ($deleted > 0) {
        echo "✓ Eliminados $deleted backup(s) antiguos\n";
    }
    
    echo "\n=== Backup completado ===\n";
} else {
    echo "✗ Error al crear backup (código: $returnCode)\n";
    exit(1);
}

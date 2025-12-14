<?php
$dbPath = __DIR__ . '/database.sqlite';
$dir = __DIR__;

echo "Files in $dir:\n";
$files = scandir($dir);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    echo "$file - " . filesize($dir . '/' . $file) . " bytes\n";
}

echo "\nChecking content of new database...\n";
if (file_exists($dbPath)) {
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
        $count = $stmt->fetchColumn();
        echo "Users count: $count\n";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Database file not found.\n";
}

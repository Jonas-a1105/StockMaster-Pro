<?php
$dbPath = __DIR__ . '/database.sqlite';

if (!file_exists($dbPath)) {
    die("❌ Error: database.sqlite not found.\n");
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking database at: " . $dbPath . "\n\n";

    // 1. Check usuarios -> remember_token
    echo "1. Checking table 'usuarios'...\n";
    $stmt = $pdo->query("PRAGMA table_info(usuarios)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    if (in_array('remember_token', $columns)) {
        echo "   ✅ 'remember_token' column FOUND.\n";
    } else {
        echo "   ❌ 'remember_token' column MISSING.\n";
    }

    // 2. Check productos -> codigo
    echo "2. Checking table 'productos'...\n";
    $stmt = $pdo->query("PRAGMA table_info(productos)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (in_array('codigo', $columns)) {
        echo "   ✅ 'codigo' column FOUND.\n";
    } else {
        echo "   ❌ 'codigo' column MISSING.\n";
    }
    
    if (in_array('codigo_barras', $columns)) {
        echo "   ✅ 'codigo_barras' column FOUND.\n";
    } else {
        echo "   ❌ 'codigo_barras' column MISSING.\n";
    }

} catch (PDOException $e) {
    echo "❌ DB Error: " . $e->getMessage() . "\n";
}

<?php
try {
    $dsn = "mysql:host=127.0.0.1;port=3306;dbname=pos;charset=utf8mb4";
    $pdo = new PDO($dsn, "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Get categories
    $stmt = $pdo->query("SELECT * FROM kategori");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "CATEGORIES:\n";
    print_r($categories);
    
    // Get menus
    $stmt = $pdo->query("SELECT m.id_menu, m.nama_menu, k.kategori FROM menu m LEFT JOIN kategori k ON m.id_kategori = k.id_kategori");
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nMENUS:\n";
    print_r($menus);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

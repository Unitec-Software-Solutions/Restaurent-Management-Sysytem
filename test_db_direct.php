<?php
// Simple test script to check database columns
$host = 'localhost';
$port = '5432';
$dbname = 'restaurant_db';
$username = 'restaurant_user';
$password = 'UnitecAdmin';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection: OK\n";
    
    // Query to get column information
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'menu_menu_items' 
        AND table_schema = 'public'
        ORDER BY ordinal_position
    ");
    
    echo "\nColumns in menu_menu_items table:\n";
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['column_name'];
        echo "  - {$row['column_name']} ({$row['data_type']}) - {$row['is_nullable']}\n";
    }
    
    // Check if specific columns exist
    $requiredColumns = ['override_price', 'sort_order', 'special_notes', 'available_from', 'available_until'];
    echo "\nRequired columns check:\n";
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $columns) ? 'YES' : 'NO';
        echo "  - $col: $exists\n";
    }
    
    // Check if old columns still exist
    $oldColumns = ['special_price', 'display_order'];
    echo "\nOld columns check:\n";
    foreach ($oldColumns as $col) {
        $exists = in_array($col, $columns) ? 'YES' : 'NO';
        echo "  - $col: $exists\n";
    }
    
    // Test basic select
    $stmt = $pdo->query("SELECT COUNT(*) FROM menu_menu_items");
    $count = $stmt->fetchColumn();
    echo "\nRows in table: $count\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

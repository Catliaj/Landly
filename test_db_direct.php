<?php
// Create a simple PDO connection directly
$config = [
    'host' => 'localhost',
    'db' => 'landlydb',
    'user' => 'root',
    'pass' => '',
];

try {
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['db']}", $config['user'], $config['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query all listings status
    $stmt = $pdo->prepare('SELECT listing_id, title, is_verified_listing, listing_status FROM land_listings ORDER BY listing_id');
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== All Listings Status ===\n";
    echo json_encode($result, JSON_PRETTY_PRINT);
    echo "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<?php
require 'vendor/autoload.php';

$config = new Config\Database();
$db = $config->initialize();

echo "=== CHECKING SELLERS AND DOCUMENTS ===\n\n";

// Check sellers
$sellers = $db->query('SELECT * FROM users WHERE roles = "seller" LIMIT 5')->getResult();
echo "Total Sellers: " . count($sellers) . "\n";

foreach ($sellers as $seller) {
    echo "\nSeller: {$seller->first_name} {$seller->last_name} (ID: {$seller->user_id})\n";
    echo "Email: {$seller->email}\n";
    echo "Active: " . ($seller->is_active ? 'Yes' : 'No') . "\n";
    
    // Check documents for this seller
    $docs = $db->query("SELECT * FROM seller_verification_documents WHERE seller_id = {$seller->user_id}")->getResult();
    echo "Documents: " . count($docs) . "\n";
    
    foreach ($docs as $doc) {
        echo "  - {$doc->document_type} (Path: {$doc->file_path})\n";
        $filePath = WRITEPATH . 'uploads/' . $doc->file_path;
        echo "    Exists: " . (file_exists($filePath) ? 'YES' : 'NO') . "\n";
    }
}

echo "\n=== CHECKING WRITABLE UPLOADS FOLDER ===\n";
$uploadsPath = WRITEPATH . 'uploads/seller_documents/';
if (is_dir($uploadsPath)) {
    $files = scandir($uploadsPath);
    echo "Files in {$uploadsPath}:\n";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file\n";
        }
    }
} else {
    echo "Folder does not exist: {$uploadsPath}\n";
}
?>

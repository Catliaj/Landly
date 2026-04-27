<?php
require 'vendor/autoload.php';
require 'preload.php';

$db = \Config\Database::connect();
$result = $db->query('SELECT listing_id, title, is_verified_listing, listing_status FROM land_listings WHERE listing_id = 1')->getResultArray();
echo json_encode($result, JSON_PRETTY_PRINT);
?>

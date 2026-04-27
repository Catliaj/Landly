<?php

require 'vendor/autoload.php';

$db = \Config\Database::connect();
$result = $db->query('SELECT listing_id, title, is_verified_listing, listing_status FROM land_listings WHERE listing_id = 1')->getResult('array');

echo "Current listing status:\n";
var_dump($result);

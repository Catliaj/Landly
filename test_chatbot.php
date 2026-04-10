<?php

// Test the chatbot endpoint
$url = 'http://localhost:8080/buyer/chatbot/send-message';
$message = 'Show me properties in Nasugbu';

$postData = http_build_query(['message' => $message]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/x-www-form-urlencoded',
            'X-Requested-With: XMLHttpRequest'
        ],
        'content' => $postData,
    ]
]);

try {
    $response = file_get_contents($url, false, $context);
    $decoded = json_decode($response, true);
    
    echo "=== CHATBOT API TEST ===\n";
    echo "Request URL: $url\n";
    echo "Request Message: $message\n";
    echo "\n=== RESPONSE ===\n";
    
    if ($decoded) {
        echo "Status: " . ($decoded['status'] ?? 'unknown') . "\n";
        echo "Message: " . substr($decoded['message'] ?? 'No message', 0, 200) . "...\n";
        echo "Listings Found: " . count($decoded['listings'] ?? []) . "\n";
        
        if (!empty($decoded['listings'])) {
            echo "\nListing Suggestions:\n";
            foreach ($decoded['listings'] as $idx => $listing) {
                echo ($idx + 1) . ". " . $listing['title'] . " - " . $listing['location'] . "\n";
            }
        }
        
        echo "\n✓ CHATBOT WORKING!\n";
    } else {
        echo "Response: " . substr($response, 0, 500) . "\n";
        if (empty($response)) {
            echo "⚠ Empty response - server might not be running or endpoint not found\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

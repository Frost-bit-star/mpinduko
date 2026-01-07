<?php
require __DIR__ . '/vendor/autoload.php';

use KyPHP\KyPHP;

/**
 * Helper to measure speed
 */
function speed(float $start): string {
    return number_format((microtime(true) - $start) * 1000, 2) . " ms";
}

// ----------------------
// Create KyPHP client
// ----------------------
$client = new KyPHP();

// ----------------------
// Example: Fetch UrbanGlam Reviews API
// ----------------------
echo "=== KyPHP API Demo: UrbanGlam Reviews ===\n\n";

$t = microtime(true);

try {
    $response = $client
        ->get('https://urbanglamhousekenya.com/api/menu')
        ->header('User-Agent', 'Mozilla/5.0')
        ->send();

    echo "HTTP Status: {$response['status']}\n";

    // Try decoding JSON
    $json = json_decode($response['body'], true);

    if (is_array($json)) {
        echo "Parsed JSON response:\n";
        print_r($json);
    } else {
        echo "⚠️ Response is not valid JSON or returned an error:\n";
        echo $response['body'] . "\n";
    }

} catch (\Exception $e) {
    echo "Request failed: " . $e->getMessage() . "\n";
}

echo "Request time: " . speed($t) . "\n";

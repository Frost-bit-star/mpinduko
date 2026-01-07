<?php
require __DIR__ . '/vendor/autoload.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ------------------------
// API endpoints
// ------------------------
if (str_starts_with($path, '/api/')) {
    $file = __DIR__ . '/api/' . basename($path) . '.php';
    if (file_exists($file)) {
        require $file;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'API endpoint not found']);
    }
    exit;
}

// ------------------------
// Frontend pages
// ------------------------
// Remove leading slash
$page = ltrim($path, '/');
// Default to index.html if empty
if ($page === '') {
    $page = 'index.html';
}

// Build full path
$file = __DIR__ . '/public/' . $page;

// Serve page if exists
if (file_exists($file) && is_file($file)) {
    echo file_get_contents($file);
} else {
    // Fallback to error.html
    $errorFile = __DIR__ . '/public/error.html';
    if (file_exists($errorFile)) {
        http_response_code(404);
        echo file_get_contents($errorFile);
    } else {
        http_response_code(404);
        echo 'Page not found';
    }
}

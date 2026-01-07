<?php
require __DIR__ . '/../vendor/autoload.php';
use KyPHP\KyPHP;

header('Content-Type: application/json; charset=utf-8');

$q = $_GET['q'] ?? null;
if (!$q) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'No query provided'
    ]);
    exit;
}

try {
    $client = new KyPHP();
    $res = $client
        ->get('https://apis.davidcyriltech.my.id/search/xvideo')
        ->query(['text' => $q])
        ->sendJson();

    // Normalize result structure
    $results = $res['result'] ?? [];

    // Rewrite download_url to stream via video.php
    foreach ($results as &$v) {
        if (!empty($v['download_url'])) {
            $v['download_url'] =
                '/api/video.php?src=' . urlencode($v['download_url']);
        }
    }

    echo json_encode([
        'success' => true,
        'result'  => $results
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Failed to fetch search results'
    ]);
}

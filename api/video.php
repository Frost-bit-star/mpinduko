<?php
require __DIR__ . '/../vendor/autoload.php';
use KyPHP\KyPHP;

header('Content-Type: application/json');

$url = $_GET['url'] ?? null;
if (!$url) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing video URL']);
    exit;
}

try {
    $client = new KyPHP();
    $res = $client
        ->get('https://apis.davidcyriltech.my.id/xvideo')
        ->query(['url' => $url])
        ->sendJson();

    echo json_encode($res);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch video info', 'msg' => $e->getMessage()]);
}

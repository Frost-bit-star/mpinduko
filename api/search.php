<?php
require __DIR__ . '/../vendor/autoload.php';
use KyPHP\KyPHP;

header('Content-Type: application/json');

$q = $_GET['q'] ?? null;
if (!$q) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing query']);
    exit;
}

try {
    $client = new KyPHP();
    $res = $client
        ->get('https://apis.davidcyriltech.my.id/search/xvideo')
        ->query(['text' => $q])
        ->sendJson();

    echo json_encode($res);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch search', 'msg' => $e->getMessage()]);
}

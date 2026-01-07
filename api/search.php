<?php
require __DIR__ . '/../vendor/autoload.php';
use KyPHP\KyPHP;

header('Content-Type: application/json');

$q = $_GET['q'] ?? null;
if (!$q) {
    http_response_code(400);
    echo json_encode(['error' => 'No query provided']);
    exit;
}

try {
    $client = new KyPHP();
    $res = $client
        ->get('https://apis.davidcyriltech.my.id/search/xvideo')
        ->query(['text' => $q])
        ->sendJson();

    // Map result to include direct download URLs for frontend
    if (!empty($res['result'])) {
        foreach ($res['result'] as &$v) {
            if (!empty($v['url'])) {
                // Fetch video metadata to get direct MP4 URL
                $videoMeta = $client
                    ->get('https://apis.davidcyriltech.my.id/xvideo')
                    ->query(['url' => $v['url']])
                    ->sendJson();

                $v['download_url'] = $videoMeta['result']['download_url'] ?? null;
            }
        }
    }

    echo json_encode($res);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch search results']);
}

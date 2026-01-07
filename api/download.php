<?php
require __DIR__ . '/../vendor/autoload.php';
use KyPHP\KyPHP;

// Get input
$url = $_GET['url'] ?? null;
$title = $_GET['title'] ?? 'video';

if (!$url) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing URL']);
    exit;
}

try {
    $client = new KyPHP();
    $res = $client
        ->get('https://apis.davidcyriltech.my.id/xvideo')
        ->query(['url' => $url])
        ->sendJson();

    if (!isset($res['download_url'])) {
        throw new Exception('Download URL missing');
    }

    $downloadUrl = $res['download_url'];
    $safeTitle = preg_replace("/[^a-zA-Z0-9_-]/", "_", $title);

    // Headers for download
    header('Content-Type: video/mp4');
    header('Content-Disposition: attachment; filename="' . $safeTitle . '.mp4"');
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Open remote file for reading in chunks
    $ch = curl_init($downloadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // send directly to output
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 1024); // 1MB chunks
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    exit;

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to download', 'msg' => $e->getMessage()]);
}

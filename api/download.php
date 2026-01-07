<?php
set_time_limit(0);
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', 'Off');

$url   = $_GET['url']   ?? null;
$title = $_GET['title'] ?? 'video';

if (!$url) {
    http_response_code(400);
    exit('Missing URL');
}

// 1️⃣ Get direct video URL
$api = 'https://apis-keith.vercel.app/download/porn?url=' . urlencode($url);
$apiRes = json_decode(file_get_contents($api), true);

if (empty($apiRes['result']['url'])) {
    http_response_code(500);
    exit('Failed to get download link');
}

$videoUrl = $apiRes['result']['url'];
$safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title);

// 2️⃣ Forward Range header
$headers = [];
if (!empty($_SERVER['HTTP_RANGE'])) {
    $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
}

// 3️⃣ Download headers
header('Access-Control-Allow-Origin: *');
header('Accept-Ranges: bytes');
header('Content-Disposition: attachment; filename="' . $safeTitle . '.mp4"');
header('Cache-Control: no-store');

$ch = curl_init($videoUrl);
curl_setopt_array($ch, [
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_BUFFERSIZE => 1024 * 1024,
    CURLOPT_WRITEFUNCTION => function ($ch, $data) {
        echo $data;
        flush();
        return strlen($data);
    },
    CURLOPT_HEADERFUNCTION => function ($ch, $header) {
        if (preg_match('/^HTTP\/.* (\d+)/', $header, $m)) {
            http_response_code((int)$m[1]);
        }
        if (preg_match('/^(Content-Length|Content-Range|Content-Type):/i', $header)) {
            header(trim($header));
        }
        return strlen($header);
    }
]);

curl_exec($ch);
curl_close($ch);
exit;

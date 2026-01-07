<?php
set_time_limit(0);
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', 'Off');

$src = $_GET['src'] ?? null;
if (!$src) {
    http_response_code(400);
    exit('Missing src');
}

// Forward Range header
$headers = [];
if (!empty($_SERVER['HTTP_RANGE'])) {
    $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
}

// Headers safe for browser + Cloudflare
header('Access-Control-Allow-Origin: *');
header('Accept-Ranges: bytes');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$ch = curl_init($src);
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

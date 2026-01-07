<?php
set_time_limit(0);
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', 'Off');

$src = $_GET['src'] ?? null;
if (!$src) {
    http_response_code(400);
    exit('No source provided.');
}

/**
 * Forward Range header (CRITICAL for <video>)
 */
$headers = [];
if (!empty($_SERVER['HTTP_RANGE'])) {
    $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
}

/**
 * REQUIRED headers for HTML5 video
 */
header('Access-Control-Allow-Origin: *');
header('Accept-Ranges: bytes');
header('Content-Type: video/mp4');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

/**
 * Stream via cURL with browser-like headers
 */
$ch = curl_init($src);
curl_setopt_array($ch, [
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_HEADER => false,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_BUFFERSIZE => 1024 * 1024,

    // 🔥 This is the key: pretend to be a browser
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
    CURLOPT_REFERER   => $src,

    CURLOPT_WRITEFUNCTION => function ($ch, $data) {
        echo $data;
        flush();
        return strlen($data);
    },

    CURLOPT_HEADERFUNCTION => function ($ch, $header) {
        // Forward critical headers from upstream
        if (preg_match('/^(HTTP\/|Content-Range:|Content-Length:|Content-Type:)/i', $header)) {
            header(trim($header));
        }
        return strlen($header);
    },
]);

curl_exec($ch);
curl_close($ch);
exit;

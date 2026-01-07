<?php
set_time_limit(0);
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', 'Off');

$src = $_GET['src'] ?? null;
if (!$src) {
    http_response_code(400);
    exit;
}

/**
 * Forward Range header (CRITICAL for <video>)
 */
$headers = [];
if (isset($_SERVER['HTTP_RANGE'])) {
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
 * Stream via cURL
 */
$ch = curl_init($src);
curl_setopt_array($ch, [
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_HEADER => false,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_BUFFERSIZE => 1024 * 1024,

    CURLOPT_WRITEFUNCTION => function ($ch, $data) {
        echo $data;
        flush();
        return strlen($data);
    },

    CURLOPT_HEADERFUNCTION => function ($ch, $header) {
        // Forward critical headers from upstream
        if (preg_match('/^(HTTP\/|Content-Range:|Content-Length:)/i', $header)) {
            header(trim($header));
        }
        return strlen($header);
    }
]);

curl_exec($ch);
curl_close($ch);
exit;

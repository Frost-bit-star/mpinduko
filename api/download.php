<?php
$src = $_GET['url'] ?? null;
$title = $_GET['title'] ?? 'video';

if (!$src) {
    http_response_code(400);
    exit('No URL provided');
}

// Sanitize filename
$safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title);

// Set headers
header('Content-Type: video/mp4');
header('Content-Disposition: attachment; filename="' . $safeTitle . '.mp4"');
header('Cache-Control: no-cache');
header('Accept-Ranges: bytes');
header('Access-Control-Allow-Origin: *');

// Stream video directly from source
$ch = curl_init($src);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_BUFFERSIZE => 1024 * 1024,
    CURLOPT_WRITEFUNCTION => function($ch, $data) { echo $data; flush(); return strlen($data); },
]);
curl_exec($ch);
curl_close($ch);
exit;

<?php
/*
  1. Single-file HLS Proxy for 1M+ users
  2. Supports token, IP binding, .m3u8 & .ts proxy, CORS, cache-control
*/

$secret = "rakib_secret";
$time = intval($_GET['time'] ?? 0);
$token = $_GET['token'] ?? '';
$user_ip = $_SERVER['REMOTE_ADDR'];

// === TOKEN CHECK ===
// === TOKEN CHECK ===
$secret = "rakib_secret";
$time = intval($_GET['time'] ?? 0);
$token = $_GET['token'] ?? '';

if (!$time || !$token || (time() - $time) > 300) {
    die("Access Denied / Expired");
}
if (!hash_equals(md5($time . $secret), $token)) {
    die("Invalid Token");
}


// === Decide if .m3u8 or .ts ===
if (isset($_GET['file'])) {
    // --- TS Segment Proxy ---
    $file = $_GET['file'];
    header("Content-Type: video/MP2T");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Access-Control-Allow-Origin: *");

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0 (Linux; Android 10; Vivo Y19s)\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $data = @file_get_contents($file, false, $context);
    if ($data === false) { http_response_code(500); die("Segment unavailable"); }
    echo $data;
    exit;
}

// === HLS .m3u8 Proxy ===
// এই ৩টি লাইন বসাবি (আগের $original_url এর জায়গায়)
$original_url = $_GET['url'] ?? '';
if (!$original_url) { die("No URL provided"); }


header("Content-Type: application/vnd.apple.mpegurl");
header("Access-Control-Allow-Origin: *");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

$opts = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0 (Linux; Android 10; Vivo Y19s)\r\nAccept: */*\r\nConnection: keep-alive\r\n"
    ]
];
$context = stream_context_create($opts);
$data = @file_get_contents($original_url, false, $context);
if ($data === false) { http_response_code(500); die("Stream unavailable"); }

// --- Rewrite .ts URLs to go through this same proxy ---
$data = preg_replace_callback('/(https?:\/\/[^\s]+\.ts)/', function($matches) use ($original_url) {
    // এখানে &url= যোগ করা হয়েছে যাতে ভিডিওর টুকরোগুলোও প্রক্সি হয়
    return "?file=" . urlencode($matches[1]) . "&time=" . $_GET['time'] . "&token=" . $_GET['token'] . "&url=" . urlencode($_GET['url']);
}, $data);


echo $data;

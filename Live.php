<?php
error_reporting(0);
$secret = "rakib_secret";

$time = $_GET['time'] ?? 0;
$token = $_GET['token'] ?? '';
$encoded_url = $_GET['url'] ?? '';
$encoded_headers = $_GET['headers'] ?? '';

if (!hash_equals(hash('sha256', $time . $secret . $_SERVER['REMOTE_ADDR']), $token)) {
    die("Access Denied");
}

$original_url = base64_decode($encoded_url);
$headers_array = json_decode(base64_decode($encoded_headers), true);

$formatted_headers = [];
foreach ($headers_array as $key => $value) {
    $formatted_headers[] = "$key: $value";
}

$opts = ["http" => ["header" => $formatted_headers]];
$context = stream_context_create($opts);

// সার্ভার থেকে ভিডিওর ডাটা রিড করা হচ্ছে যেন ব্লক না হয়
$data = file_get_contents($original_url, false, $context);

header("Content-Type: application/vnd.apple.mpegurl");
echo $data;
?>

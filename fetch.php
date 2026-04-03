<?php
/**
 * AXiRON Sports Pro - High Performance Proxy
 * Designed for HLS (.m3u8) Streaming
 */

// ১. সব ধরনের অরিজিন (App/Web) থেকে রিকোয়েস্ট এলাউ করা (CORS Fix)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/vnd.apple.mpegurl"); // M3U8 ফরম্যাট সেট করা

// ২. ইনপুট লিঙ্ক চেক করা
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No Stream ID (URL) provided.");
}

$stream_url = $_GET['id'];

// ৩. লিঙ্কের ভ্যালিডেশন (নিরাপত্তার জন্য)
if (!filter_var($stream_url, FILTER_VALIDATE_URL)) {
    die("Error: Invalid Stream URL.");
}

// ৪. সোর্স ওয়েবসাইটকে বুঝানো যে এটি একটি আসল ব্রাউজার (User-Agent)
$options = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36\r\n" .
                    "Referer: https://google.com/\r\n" .
                    "Accept: */*\r\n"
    ]
];

$context = stream_context_create($options);

// ৫. সোর্স থেকে ডাটা নিয়ে আসা এবং সরাসরি আউটপুট দেওয়া
$data = @file_get_contents($stream_url, false, $context);

if ($data === false) {
    header("HTTP/1.1 404 Not Found");
    echo "#EXTM3U\n#EXT-X-ERROR: Could not fetch stream data.";
} else {
    // যদি ডাটা পাওয়া যায়, তবে তা প্রিন্ট করা
    echo $data;
}
?>

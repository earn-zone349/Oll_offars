<?php
/**
 * AXiRON Sports Pro - Premium Header Proxy
 * Optimized for Toffee and HLS Streams
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/vnd.apple.mpegurl");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No Stream URL provided.");
}

$stream_url = $_GET['id'];

// ১. ছবির মতো করে স্পেশাল হেডার এবং কুকি এখানে সেট করা হয়েছে
$headers = [
    "User-Agent: Toffee (Linux; Android 14) AndroidXMedia3/1.1.1/64103898/4d2ec8b8c7534adc",
    "Host: bldcmprod-cdn.toffeelive.com",
    "Connection: Keep-Alive",
    "Accept-Encoding: gzip",
    // নিচের কুকিটি Toffee-র ভিডিও চালানোর জন্য সবথেকে জরুরি
    "Cookie: Edge-Cache-Cookie=URLPrefix=aHR0cHM6Ly9ibGRjbXByb2QtY2RuLnRvZmZlZWxpdmUuY29tL2Nkbbi8;Expires=1705020493;KeyName=prod_linear;Signature=w2ySD7"
];

// ২. স্ট্রিম রিকোয়েস্ট কনফিগার করা
$options = [
    "http" => [
        "method" => "GET",
        "header" => implode("\r\n", $headers) . "\r\n"
    ]
];

$context = stream_context_create($options);

// ৩. সোর্স থেকে ভিডিও ডাটা রিড করা
$data = @file_get_contents($stream_url, false, $context);

if ($data === false) {
    header("HTTP/1.1 404 Not Found");
    echo "#EXTM3U\n#EXT-X-ERROR: Could not fetch premium stream.";
} else {
    // ৪. আউটপুট সরাসরি প্লেয়ারে পাঠিয়ে দেওয়া
    echo $data;
}
?>

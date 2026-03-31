<?php

error_reporting(0);

// === CONFIG ===
$secret = "rakib_secret";

// === INPUT ===
$time = intval($_GET['time'] ?? 0);
$token = $_GET['token'] ?? '';
$user_ip = $_SERVER['REMOTE_ADDR'];

// === SECURITY CHECK ===
if (!$time || !$token || (time() - $time) > 300) {
    die("Access Denied / Expired");
}

// ✅ SAME LOGIC as fetch.php
if (!hash_equals(md5($time . $secret . $user_ip), $token)) {
    die("Invalid Token");
}

// === TS FILE PROXY ===
if (isset($_GET['file'])) {

    $file = $_GET['file'];

    header("Content-Type: video/MP2T");
    header("Access-Control-Allow-Origin: *");

    $data = @file_get_contents($file);

    if ($data === false) {
        http_response_code(500);
        die("Segment unavailable");
    }

    echo $data;
    exit;
}

// === MAIN STREAM ===
$encoded = $_GET['url'] ?? '';
if (!$encoded) die("No URL");

// 🔐 Decode (if base64 used)
$original_url = base64_decode($encoded);

// === FETCH PLAYLIST ===
header("Content-Type: application/vnd.apple.mpegurl");
header("Access-Control-Allow-Origin: *");

$data = @file_get_contents($original_url);

if (!$data) {
    http_response_code(500);
    die("Stream error");
}

// === FIX RELATIVE PATH ===
$base = dirname($original_url);

// === Rewrite TS ===
$data = preg_replace_callback('/([^"\']+\.ts)/', function($m) use ($base, $time, $token, $encoded) {

    $segment = $m[1];

    if (strpos($segment, 'http') !== 0) {
        $segment = $base . '/' . $segment;
    }

    return "?file=" . urlencode($segment) .
           "&time=$time&token=$token&url=" . urlencode($encoded);

}, $data);

echo $data;

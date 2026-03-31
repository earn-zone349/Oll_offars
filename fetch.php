<?php

// Error hide (security)
error_reporting(0);

// ১. Channel ID নেওয়া
$channel_id = $_GET['id'] ?? '';

if (empty($channel_id)) {
    die("Channel ID missing!");
}

// ২. GitHub JSON
$source_url = "https://raw.githubusercontent.com/byte-capsule/Toffee-Channels-Link-Main/main/toffee_links.json";

// Timeout + fallback
$context = stream_context_create([
    "http" => [
        "timeout" => 5
    ]
]);

$json_data = @file_get_contents($source_url, false, $context);

if (!$json_data) {
    die("Source load failed!");
}

$channels = json_decode($json_data, true);

if (!$channels) {
    die("Invalid JSON!");
}

$actual_link = "";

// ৩. Channel খোঁজা
foreach ($channels as $channel) {
    if (isset($channel['name']) && strtolower($channel['name']) === strtolower($channel_id)) {
        $actual_link = $channel['link'];
        break;
    }
}

if (empty($actual_link)) {
    die("Channel not found!");
}

// --- 🔐 Security Part ---
$secret = "rakib_secret";
$time = time();
$user_ip = $_SERVER['REMOTE_ADDR'];

// Strong token
$token = md5($time . $secret . $user_ip);

// ৫. Redirect
$redirect_url = "Live.php?time=$time&token=$token&url=" . urlencode($actual_link);

// ৬. Redirect
header("Location: $redirect_url");
exit;

?>

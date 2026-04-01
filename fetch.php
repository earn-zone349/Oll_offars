<?php
error_reporting(0);
$channel_id = $_GET['id'] ?? '';
if (empty($channel_id)) die("Channel ID missing!");

// তোর দেওয়া স্ক্রিনশটের সেই আসল গিটহাব JSON লিঙ্ক
$source_url = "https://raw.githubusercontent.com/gtajisan/Toffee-Auto-Update-Playlist/main/toffee.json";

$json_data = file_get_contents($source_url);
$data = json_decode($json_data, true);

$actual_link = "";
$headers = [];

foreach ($data['channels'] as $channel) {
    if (isset($channel['name']) && strtolower($channel['name']) === strtolower($channel_id)) {
        $actual_link = $channel['link'];
        $headers = $channel['headers']; // কুকি আর হোস্ট এখানে আছে
        break;
    }
}

if (empty($actual_link)) die("Channel not found!");

$secret = "rakib_secret";
$time = time();
$token = hash('sha256', $time . $secret . $_SERVER['REMOTE_ADDR']);

// লিঙ্ক আর হেডার এনকোড করে Live.php-তে পাঠানো হচ্ছে
$redirect_url = "Live.php?time=$time&token=$token&url=" . base64_encode($actual_link) . "&headers=" . base64_encode(json_encode($headers));
header("Location: $redirect_url");
exit;
?>

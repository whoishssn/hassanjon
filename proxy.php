<?php
$bot_token       = getenv('7171638404:AAGsiMFdwvhXZQMRrzY_XCrsEv9zURBSNEU');
$source_channel  = getenv('@ProxyMTProto');
$target_channel  = getenv('@proxy_who');

if (!$bot_token || !$source_channel || !$target_channel) {
    exit("خطا: مقادیر محیطی BOT_TOKEN یا SOURCE_CHANNEL یا TARGET_CHANNEL تنظیم نشده‌اند.\n");
}

$updates = file_get_contents("https://api.telegram.org/bot$bot_token/getUpdates");

if (!$updates) {
    exit("خطا در دریافت پیام‌ها از ربات تلگرام.\n");
}

$data = json_decode($updates, true);

$proxies = [];

foreach ($data['result'] as $update) {
    if (isset($update['message']['text'])) {
        $text = $update['message']['text'];
        preg_match_all('/https:\/\/t\.me\/proxy\?server=[^\s]+/', $text, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $link) {
                if (!in_array($link, $proxies)) {
                    $proxies[] = $link;
                }
            }
        }
    }
}

if (!empty($proxies)) {
    foreach ($proxies as $proxy) {
        $url = "https://api.telegram.org/bot$bot_token/sendMessage";
        $params = [
            'chat_id' => $target_channel,
            'text'    => $proxy,
            'disable_web_page_preview' => true
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($params)
            ]
        ];

        $context  = stream_context_create($options);
        file_get_contents($url, false, $context);
    }

    echo "ارسال شد: " . count($proxies) . " لینک پروکسی به $target_channel\n";
} else {
    echo "هیچ لینک پروکسی جدیدی یافت نشد.\n";
}

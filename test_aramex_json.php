<?php
function testAramexPublicJSON()
{
    $url = 'https://www.aramex.com/api/track/tracking/track';
    $data = [
        'trackingNumbers' => ['37301449765'],
        'language' => 'en'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ]);

    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    echo "Status: " . $info['http_code'] . "\n";
    echo "Response:\n" . substr($res, 0, 1000);
}

testAramexPublicJSON();
?>
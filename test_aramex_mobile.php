<?php
function testAramexMobile()
{
    $url = 'https://mobilev2.aramex.com/TrackShipments';
    $data = [
        'Shipments' => ['37301449765'],
        'GetLastTrackingUpdateOnly' => false
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: AramexMobile/1.0',
    ]);

    $res = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    echo "Status: " . $info['http_code'] . "\n";
    echo "Error: $err\n";
    echo "Response:\n" . substr($res, 0, 1000);
}

testAramexMobile();
?>
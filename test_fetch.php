<?php
$url = 'https://www.aramex.com/ae/en/track/results?source=aramex&ShipmentNumber=37301449765';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
$res = curl_exec($ch);
file_put_contents('arame_test.html', $res);
echo "Length: " . strlen($res);
?>
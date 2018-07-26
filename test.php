<?php

$ch = curl_init( "https://api.weather.yandex.ru/v1/informers?lat=56.618007&lon=57.779208");
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
curl_setopt( $ch, CURLOPT_TIMEOUT, 3 );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-Yandex-API-Key: 90b3db9b-dcf5-4b5e-b8e0-8c14d83620a9'));

$response = json_decode(curl_exec( $ch ));
$error_code = curl_errno($ch);
curl_close($ch);
echo "<pre>";
var_dump($response);
echo "</pre>";
echo "<br>";
echo "icon= ".$response->forecast->parts[1]->icon ;

function DownloadImages($url, $filename){
    $ch = curl_init($url);
    $fp = fopen($filename, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);

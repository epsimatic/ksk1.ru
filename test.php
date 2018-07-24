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
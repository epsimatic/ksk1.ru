<?php
$json_string = file_get_contents("http://api.wunderground.com/api/14a26adef7c89cc2/geolookup/conditions/lang:RU/q/Russia/Krasnoufimsk.json");
$parsed_json = json_decode($json_string);
$location = $parsed_json->{'location'}->{'city'};
$temp_c = intval($parsed_json->{'current_observation'}->{'temp_c'});
$pressure = round(intval($parsed_json->{'current_observation'}->{'pressure_mb'}) * 0.7500637554192);
$wind = round(intval($parsed_json->{'current_observation'}->{'wind_kph'}) / 3.6);
$humidity = $parsed_json->{'current_observation'}->{'relative_humidity'};
$feelslike_c = intval($parsed_json->{'current_observation'}->{'feelslike_c'});
if ($temp_c == $feelslike_c) $temp = 'Температура ' . $temp_c . '°';
else  $temp = 'Температура ' . $temp_c . '°, Ощущается как ' . $feelslike_c . '°';
if (is_nan($temp_c)) {
    header("Status: 503 Internal server error");
    die ('Error saving weather.html temp_c isNAN');
}
$icon_url = $parsed_json->{'current_observation'}->{'icon_url'};
if ($temp_c > 0) $sign = "+"; else $sign = "";
$text = '<div class="weather-block" title="По данным на ' .
    date("G.i", $parsed_json->{'current_observation'}->{'observation_epoch'}) . ':' . PHP_EOL
    . $temp . '
Давление ' . $pressure . ' мм рт.ст.
Ветер ' . $wind . ' м/с
Влажность ' . $humidity . PHP_EOL
    . $parsed_json->{'current_observation'}->{'weather'} . '
Щёлкните для прогноза">
            <img class="weather-icon" src="' . $icon_url . '">
            <div class="weather-temp">' . $sign . $temp_c . '</div>
            <div class="weather-label">' . $parsed_json->{'current_observation'}->{'weather'} . '</div>
        </div>';

if (file_put_contents("weather.html", $text)) {
    echo "File weather.html saved";
} else {
    header("Status: 503 Internal server error");
    die ('Error saving weather.html');
}


/*echo "<p>Current temperature in ${location} is: ${temp_c}</p>";
echo "<p>";

echo date("G:i",($parsed_json->{'current_observation'}->{'observation_epoch'}));

echo "</p>";*/


//echo "time =".date("G:i",($parsed_json->{'current_observation'}->{'local_epoch'}+21600));
//echo date("Z");
?>

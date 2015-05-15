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
$description = $parsed_json->{'current_observation'}->{'weather'};
$icon = $parsed_json->{'current_observation'}->{'icon'};
$icon_url = $parsed_json->{'current_observation'}->{'icon_url'};
$img_weather = '<img class="weather-icon" src="' . $icon_url . '">';
if (is_nan($temp_c) || $temp_c === null || $description=="" ||$icon=="" ) {
    header("Status: 503 Internal server error");
    echo 'Weatherunderground is offline, using Yandex';
   $text = '<div class="ya-weather"><img alt="Погода" src="//info.weather.yandex.net/krasnoufimsk/3_white.ru.png?domain=ru"></div>';
} else {
//$icon_url = "http://icons.wxug.com/i/c/k/clear.gif";
if ($temp_c > 0) $sign = "+"; else $sign = "";
$week = array(
    "Sunday" => "воскресенье",
    "Monday" => "понедельник",
    "Tuesday" => "вторник",
    "Wednesday" => "среду",
    "Thursday" => "четверг",
    "Friday" => "пятницу",
    "Saturday" => "субботу");
$text = '<div class="weather-block" title="По данным на ' . $week[date("l", $parsed_json->{'current_observation'}->{'observation_epoch'})] . " в " .
    date("G.i", $parsed_json->{'current_observation'}->{'observation_epoch'}) . ':' . PHP_EOL
    . $temp . '
Давление ' . $pressure . ' мм рт.ст.
Ветер ' . $wind . ' м/с
Влажность ' . $humidity . PHP_EOL
    . $description . '
Щёлкните для прогноза">
            <img class="weather-icon" src="' . $icon_url . '">
            <div class="weather-temp">' . $sign . $temp_c . '</div>
            <div class="weather-label">' . $description . '</div>
        </div>';
}

//echo "\nTemp = ".$temp_c . "\nDesc = " . $description . "\nIcon = " . $icon . "\n\n";

//var_dump($parsed_json); echo "\n\n";

if (file_put_contents("weather.html", $text)) {
//    echo "File weather.html saved";
} else {
    header("Status: 503 Internal server error");
    die ('Error saving weather.html');
}

//echo "\n\nForecast:\n\n";

//var_dump(json_decode(file_get_contents("http://api.wunderground.com/api/14a26adef7c89cc2/geolookup/forecast/lang:RU/q/Russia/Krasnoufimsk.json")));
//var_dump(file_get_contents("http://api.wunderground.com/api/14a26adef7c89cc2/geolookup/forecast/lang:RU/q/Russia/Krasnoufimsk.json"));

/*echo "<p>Current temperature in ${location} is: ${temp_c}</p>";
echo "<p>";*/?>

<html><head>
<link href="http://ksk1.ru/vendor/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<script src="http://ksk1.ru/vendor/bootstrap/dist/js/bootstrap.min.js" type="application/javascript"></script>
<link href="http://ksk1.ru/style.css" rel="stylesheet" type="text/css"/>
</head><body>
<div class="container">
    <h2>Тестовая страница, например</h2>
    <div id="header">
        <div id="navpanel-info" class="navpanel navpanel-info row active">
            <div class="col-xs-12 col-sm-4 subpanel cat">
                <div class="col-xs-12 subpanel" id="weather-panel">
                    <div class="day-row">
                        <div class="summary">
                            <span class="weekday">Пятница</span>
                            <span class="date">15 мая</span>
		                    <span class="temps">
		                        <span class="high">20</span>
                                <span class="split">|</span>
		                        <span class="low">7</span>
		                        °C
		                    </span>
                            <span title="Вероятность осадков" class="pop" style="rgba(100, 181, 246, .4)">
                                <span class="drop-icon">💧</span>
                                <strong>0</strong> мм
                            </span>
                        </div>
                        <div class="day">
                            <img src="//icons.wxug.com/i/c/v1/partlycloudy.svg">
                            <p>Переменная облачность. Повышение 20C. Ветер ЮВ от 10 до 15 км/ч.</p>
                        </div>
                        <div class="night">
                            <img src="//icons.wxug.com/i/c/v1/nt_chancerain.svg">
                            <p><em>Ночью</em> проливные дожди позднее вечером. Понижение 7C. Ветер В и переменный. Вероятность дождя 40%.</p>
                        </div>
                    </div>

                    <div class="day-row ">
                        <div class="summary">
                            <span class="weekday">Суббота</span>
                            <span class="date">16 мая</span>
		                    <span class="temps">
		                        <span class="high">18</span>
		                        <span class="split">|</span>
		                        <span class="low">8</span>
		                        °C
                            </span>
                            <span title="Вероятность осадков" class="pop" style="rgba(100, 181, 246, .8)">
                                <span class="drop-icon">💧</span>
                                <strong>6</strong> мм
                            </span>
                        </div>
                        <div class="day">
                            <img src="//icons.wxug.com/i/c/v1/rain.svg">
                            <p>Дождь. Повышение 19C. Ветер В от 10 до 15 км/ч. Вероятность дождя 80%.</p>
                        </div>
                        <div class="night">
                            <img src="//icons.wxug.com/i/c/v1/nt_rain.svg">
                            <p><em>Ночью</em> дождь. Понижение 9C. Ветер ВЮВ и переменный. Вероятность дождя 80%. Осадки примерно ~ 6 мм.</p>
                        </div>
                    </div>

                    <div id="fctDay-20150517" class="day-row ">
                        <div class="summary">
                            <span class="weekday">Воскресенье</span>
                            <span class="date">17 мая</span>
                            <span class="temps">
                                <span class="high">17</span>
                                <span class="split">|</span>
                                <span class="low">4</span>
                                °C
                            </span>
                            <span title="Вероятность осадков" class="pop" style="rgba(100, 181, 246, .7)">
                                <span class="drop-icon">💧</span>
                                <strong>2</strong> мм
                            </span>
                        </div>
                        <div class="day">
                            <img src="//icons.wxug.com/i/c/v1/chancerain.svg">
                            <p>Проливные дожди. Повышение 17C. Ветер ЮЮЗ от 10 до 15 км/ч. Вероятность дождя 70%.</p>
                        </div>
                        <div class="night">
                            <img src="//icons.wxug.com/i/c/v1/nt_partlycloudy.svg">
                            <p><em>Ночью</em> переменная облачность. Понижение 5C. Ветер Ю от 10 до 15 км/ч.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h3>А вот сырые данные:</h3>
    <pre><?=file_get_contents("http://api.wunderground.com/api/14a26adef7c89cc2/geolookup/forecast/lang:RU/q/Russia/Krasnoufimsk.json");?></pre>
</div>
</body></html>


<?


//echo "time =".date("G:i",($parsed_json->{'current_observation'}->{'local_epoch'}+21600));
//echo date("Z");
?>

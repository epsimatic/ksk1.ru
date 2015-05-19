<html>
<head>
    <meta charset="utf-8">
    <link href="http://ksk1.ru/vendor/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <script src="http://ksk1.ru/vendor/bootstrap/dist/js/bootstrap.min.js" type="application/javascript"></script>
    <link href="http://ksk1.ru/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="http://ksk1.ru/style.css" rel="stylesheet" type="text/css"/>
</head>

<body>
<div class="container">

<?php
$json_string = file_get_contents("http://api.wunderground.com/api/14a26adef7c89cc2/geolookup/conditions/forecast/lang:RU/q/Russia/Krasnoufimsk.json");
$parsed_json = json_decode($json_string);
$location = $parsed_json->{'location'}->{'city'};
$parsed_conditions = $parsed_json->{'current_observation'};

$temp_c = intval($parsed_conditions->{'temp_c'});
$pressure = round(intval($parsed_conditions->{'pressure_mb'}) * 0.7500637554192);
$wind = round(intval($parsed_conditions->{'wind_kph'}) / 3.6);
$humidity = $parsed_conditions->{'relative_humidity'};
$feelslike_c = intval($parsed_conditions->{'feelslike_c'});
if ($temp_c == $feelslike_c) $temp = 'Температура ' . $temp_c . '°';
else  $temp = 'Температура ' . $temp_c . '°, Ощущается как ' . $feelslike_c . '°';
$description = $parsed_conditions->{'weather'};
$icon = $parsed_conditions->{'icon'};
$icon_url = $parsed_conditions->{'icon_url'};
$img_weather = '<img class="weather-icon" src="' . $icon_url . '">';
if (is_nan($temp_c) || $temp_c === null /*|| $description == ""*/ || $icon == "") {
    header("Status: 503 Internal server error");
    echo '<p class="bg-danger">Weatherunderground is offline, using Yandex</p>';
    echo "<pre> temp_c = $temp_c \n description = $description \n icon = $icon</pre>";
    echo "<h1>Сырые данные:</h1><pre>"; print_r($parsed_json); echo "</pre>";
    $conditions = '<div class="ya-weather"><img alt="Погода" src="//info.weather.yandex.net/krasnoufimsk/3_white.ru.png?domain=ru"></div>';
} else {
    if ($temp_c > 0) $sign = "+"; else $sign = "";
    $week = array(
        "Sunday" => "воскресенье",
        "Monday" => "понедельник",
        "Tuesday" => "вторник",
        "Wednesday" => "среду",
        "Thursday" => "четверг",
        "Friday" => "пятницу",
        "Saturday" => "субботу");
    $conditions = '<div class="weather-block" title="По данным на ' . $week[date("l", $parsed_conditions->{'observation_epoch'})] . " в " .
        date("G.i", $parsed_conditions->{'observation_epoch'}) . ':' . PHP_EOL
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

if (file_put_contents("conditions.html", $conditions)) {
    echo "File <a href='/weather/conditions.html'>conditions.html</a> saved";
} else {
    header("Status: 503 Internal server error");
    echo "Error saving <a href='/weather/conditions.html'>conditions.html</a>";
}

//echo "\n\nForecast:\n\n";

//var_dump(json_decode(file_get_contents("http://api.wunderground.com/api/14a26adef7c89cc2/geolookup/forecast/lang:RU/q/Russia/Krasnoufimsk.json")));
//var_dump(file_get_contents("http://api.wunderground.com/api/14a26adef7c89cc2/geolookup/forecast/lang:RU/q/Russia/Krasnoufimsk.json"));

$array_forecast = array();
$json_forecast = file_get_contents("http://api.wunderground.com/api/14a26adef7c89cc2/geolookup/forecast/lang:RU/q/Russia/Krasnoufimsk.json");
$parsed_forecast = json_decode($json_forecast);
//общие данные
$simpleforecastdays = $parsed_forecast->{'forecast'}->{'simpleforecast'}->{'forecastday'};
//echo '<pre>'; var_dump($forecastdays); echo '</pre>';
$month = array(
    1 => "января",
    2 => "февраля",
    3 => "марта",
    4 => "апреля",
    5 => "мая",
    6 => "июня",
    7 => "июля",
    8 => "августа",
    9 => "сентября",
    10 => "октября",
    11 => "ноября",
    12 => "декабря",
);
$obect = 0;
foreach ($simpleforecastdays as $forecastday) {
    $array_forecast[$obect]['weekday'] = $forecastday->{'date'}->{'weekday'};
    $array_forecast[$obect]['day'] = $forecastday->{'date'}->{'day'} . " " . $month[$forecastday->{'date'}->{'month'}];
    $array_forecast[$obect]['temp_high'] = $forecastday->{'high'}->{'celsius'};
    $array_forecast[$obect]['temp_low'] = $forecastday->{'low'}->{'celsius'};
    $array_forecast[$obect]['conditions'] = $forecastday->{'conditions'};
    $array_forecast[$obect]['mm'] = $forecastday->{'qpf_allday'}->{'mm'};
    $array_forecast[$obect]['pop'] = $forecastday->{'pop'} / 100;
    $obect++;
}
//данные день-ночь
$forecasts = $parsed_forecast->{'forecast'}->{'txt_forecast'}->{'forecastday'};
foreach ($forecasts as $forecast) {
    $period = $forecast->{'period'};
    if ($period % 2) {
        $object_num = intval(($period - 1) / 2);
        $array_forecast[$object_num]['text_night'] = $forecast->{'fcttext_metric'};
        $array_forecast[$object_num]['icon_url_night'] = $forecast->{'icon_url'};
    } else {
        $object_num = intval(($period) / 2);
        $array_forecast[$object_num]['text_day'] = $forecast->{'fcttext_metric'};
        $array_forecast[$object_num]['icon_url_day'] = $forecast->{'icon_url'};
    }
}
array_pop($array_forecast);
$conditions_forecast = "";
foreach ($array_forecast as $forecast_object) {
    //Первую букву -- маленькой
    if ( $forecast_object['text_night'][1] < 0x30 )
        $forecast_object['text_night'][1] += 0x20;
    $conditions_forecast .= " <div class='day-row'>
                        <div class='summary'>
                            <span class='weekday'>" . $forecast_object['weekday'] . "</span>
                            <span class='date'>" . $forecast_object['day'] . "</span>
		                    <span class='temps'>
		                        <span class='high'>" . $forecast_object['temp_high'] . "</span>
                                <span class='split'>|</span>
		                        <span class='low'>" . $forecast_object['temp_low'] . "</span>
		                        °C
		                    </span>";
    if ($forecast_object['mm'] > 0 || $forecast_object['pop'] > 0)
        $conditions_forecast .= "<span title='Вероятность осадков' class='pop' style='background-color: rgba(41, 182, 246, " . $forecast_object['pop'] . ");'>
                            <span class='drop-icon'></span>
                                <strong>" . $forecast_object['mm'] . "</strong> мм</span>";

    else 
        $conditions_forecast .= "<span title='Вероятность осадков' class='pop pop-dry'>Сухо</span>";
    $conditions_forecast .= "</div>
                        <div class='day'>
                            <img src='" . $forecast_object['icon_url_day'] . "'>
                            <p>" . $forecast_object['text_day'] . "</p>
                        </div>
                        <div class='night'>
                            <img src='" . $forecast_object['icon_url_night'] . "'>
                            <p><em>Ночью</em> " . lcfirst($forecast_object['text_night']) . "</p>
                        </div>
                    </div>";


}


$conditions_forecast .= "<h6 class='text-center'><a href='http://www.wunderground.com/q/zmw:00000.1.28434' target='_blank'>
Подробный прогноз погоды на 10 дней <i class='fa fa-arrow-right'></i></a></h6>";

//echo $conditions_forecast;
//var_dump($array_forecast);

if (/*is_nan($temp_c) || $temp_c === null || $description == "" || $icon == "" */ false) {
    header("Status: 503 Internal server error");
    echo 'Weatherunderground (forecast) is offline, using Yandex';
    $forecast = '<a class="ya-weather-forecast" href="https://pogoda.yandex.ru/krasnoufimsk" target="_blank">
                    <img alt="Погода" src="//info.weather.yandex.net/krasnoufimsk/2_white.ru.png?domain=ru">
                 </a>';
} else {
    $forecast = $conditions_forecast;
}

if (file_put_contents("forecast.html", $forecast)) {
    echo "File <a href='/weather/forecast.html'>forecast.html</a> saved";
} else {
    header("Status: 503 Internal server error");
    die ("Error saving <a href='/weather/forecast.html'>forecast.html</a>");
}


?>


    <?=$conditions?>

<div id='header'>
<div id='navpanel-info' class='navpanel navpanel-info row active'>
<div class='col-xs-12 col-sm-4 subpanel cat'>
<div class='col-xs-12 subpanel' id='weather-panel'>

    <?=$forecast?>

</div></div></div></div>


</div>
</body>
</html>


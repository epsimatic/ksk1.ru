<html> <!--запускается каждый час-->
<head>
    <meta charset="utf-8">
    <link href="https://ksk1.ru/vendor/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <script src="https://ksk1.ru/vendor/bootstrap/dist/js/bootstrap.min.js" type="application/javascript"></script>
    <link href="https://ksk1.ru/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="https://ksk1.ru/style.css" rel="stylesheet" type="text/css"/>
</head>

<body>
<div class="container">

<?php
error_reporting(-1);
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
function mbStringToArray ($string) {
    $stop = mb_strlen($string);
    $result = array();
    for ($idx = 0; $idx < $stop; $idx++) {
        $result[] = mb_substr($string, $idx, 1);
    }
    return $result;
}

function prettyNotice($text, $class="info") {
    echo "<div class='alert alert-$class' style='margin-top: 10px; margin-bottom: 10px;'>$text</div>\n\n";
}

function updateIconUrl($orig_url) {
    return str_replace('/k/', '/v4/', str_replace('.gif', '.svg', $orig_url));    
}

$json_string = file_get_contents("https://api.wunderground.com/api/14a26adef7c89cc2/geolookup/conditions/forecast/lang:RU/q/Russia/Krasnoufimsk.json");
$parsed_json = json_decode($json_string);
$location = $parsed_json->{'location'}->{'city'};

// Текущее состояние

$parsed_conditions = $parsed_json->{'current_observation'};

$pressure = round(intval($parsed_conditions->{'pressure_mb'}) * 0.7500637554192);
$wind = round(intval($parsed_conditions->{'wind_kph'}) / 3.6);
$humidity = $parsed_conditions->{'relative_humidity'};

// Температура
$temp_c = intval($parsed_conditions->{'temp_c'});
$feelslike_c = intval($parsed_conditions->{'feelslike_c'});
$temp = ($temp_c == $feelslike_c) ?
        "Температура ${temp_c}℃" :
        "Температура ${temp_c}℃ (ощущается как ${feelslike_c}℃)";

// Первую букву — большой
$description = mb_convert_case($parsed_conditions->{'weather'}, MB_CASE_LOWER );
$description  = mbStringToArray($description);
$description[0] = mb_convert_case($description[0], MB_CASE_UPPER);
$description = implode("", $description);

$icon = $parsed_conditions->{'icon'};
$icon_url = str_replace('http:','',updateIconUrl($parsed_conditions->{'icon_url'}));
$img_weather = '<img class="weather-icon" src="' . $icon_url . '" alt="Значок погоды">';
if (is_nan($temp_c) || $temp_c === null /*|| $description == ""*/ || $icon == "") {
    header("Status: 503 Internal server error");
    prettyNotice("Weatherunderground сломан, используем Яндекс<br>
                  <pre> temp_c = $temp_c \n description = $description \n icon = $icon</pre>", "danger");
    echo "<h1>Сырые данные:</h1><pre>"; print_r($parsed_json); echo "</pre>";

    /*$conditions = '<div class="ya-weather"><img src="//info.weather.yandex.net/krasnoufimsk/3_white.ru.png?domain=ru"
                                                alt="Погода" ></div>';*/
    $conditions='
<link rel="stylesheet" type="text/css" href="https://nst1.gismeteo.ru/assets/flat-ui/legacy/css/informer.min.css">

<div id="gsInformerID-7S5nnOq1O06pY5" class="gsInformer" style="width:125px;height:68px">
    <div class="gsIContent">
        <div id="cityLink">
            <a href="https://www.gismeteo.ru/weather-krasnoufimsk-4515/" target="_blank" title="Погода в Красноуфимске"><img src="https://nst1.gismeteo.ru/assets/flat-ui/img/gisloader.svg" width="24" height="24" alt="Погода в Красноуфимске"></a>
        </div>
        <div class="gsLinks">
            <table>
                <tr>
                    <td>
                        <div class="leftCol">
                            <a href="https://www.gismeteo.ru/" target="_blank" title="Погода в Красноуфимске">
                                <img alt="Погода в Красноуфимске" src="https://nst1.gismeteo.ru/assets/flat-ui/img/logo-mini2.png" align="middle" border="0" width="11" height="16" />
                                <img src="https://nst1.gismeteo.ru/assets/flat-ui/img/informer/gismeteo.svg" border="0" align="middle" style="left: 5px; top:1px">
                            </a>
                            </div>
                            <div class="rightCol">
                                <a href="https://www.gismeteo.ru/weather-krasnoufimsk-4515/2-weeks/" target="_blank" title="Погода в Красноуфимске на 2 недели">
                                    <img src="https://nst1.gismeteo.ru/assets/flat-ui/img/informer/forecast-2weeks.ru.svg" border="0" align="middle" style="top:auto" alt="Погода в Красноуфимске на 2 недели">
                                </a>
                            </div>
                                            </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<script async src="https://www.gismeteo.ru/api/informer/getinformer/?hash=7S5nnOq1O06pY5" type="text/javascript"></script>';
}
else {
    $sign = ($temp_c > 0) ? "+" : "";
    $week = array(
        "Sunday" => "воскресенье",
        "Monday" => "понедельник",
        "Tuesday" => "вторник",
        "Wednesday" => "среду",
        "Thursday" => "четверг",
        "Friday" => "пятницу",
        "Saturday" => "субботу");
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
    $conditions = '<div class="weather-text">'
                . '<div class="weather-date">' .date("j", $parsed_conditions->{'observation_epoch'})." ".$month[date("n", $parsed_conditions->{'observation_epoch'})] . '</div>'
           //     . '<div class="weather-label">' . $description . '</div>'
                . '<div class="weather-detail"><a href="https://pogoda.yandex.ru/krasnoufimsk/details" >Подробнее</a></div></div>'
                . '<div class="weather-block" title="По данным на '
                . $week[date("l", $parsed_conditions->{'observation_epoch'})] . " в "
                . date("G.i", $parsed_conditions->{'observation_epoch'}) . ':' . PHP_EOL
                . $temp . PHP_EOL
                . 'Давление ' . $pressure . ' мм рт.ст.' . PHP_EOL
                . 'Ветер ' . $wind . ' м/с' . PHP_EOL
                . 'Влажность ' . $humidity . PHP_EOL
                . $description . PHP_EOL
                . 'Щёлкните для прогноза">'
                . '<img class="weather-icon" src="' . $icon_url . '">'
                . '<div class="weather-temp">' . $sign . $temp_c . '</div>'
                . '<div class="weather-label">' . $description . '</div></div>';
}

if (file_put_contents("conditions.html", $conditions)) {
    prettyNotice("Сохранён файл <a href='/weather/conditions.html'>conditions.html</a>");
} else {
    header("Status: 503 Internal server error");
    prettyNotice("Не удалось сохранить <a href='/weather/conditions.html'>conditions.html</a>","danger");
}



// Прогноз на 3 дня

//общие данные
$simpleforecastdays = $parsed_json->{'forecast'}->{'simpleforecast'}->{'forecastday'};
//echo '<pre>'; var_dump($forecastdays); echo '</pre>';


$array_forecast = array();

$day_num = 0;
foreach ($simpleforecastdays as $forecastday) {
    $array_forecast[$day_num]['weekday'] = $forecastday->{'date'}->{'weekday'};
    $array_forecast[$day_num]['day'] = $forecastday->{'date'}->{'day'} ." ". $month[$forecastday->{'date'}->{'month'}];
    $array_forecast[$day_num]['temp_high'] = $forecastday->{'high'}->{'celsius'};
    $array_forecast[$day_num]['temp_low'] = $forecastday->{'low'}->{'celsius'};
    $array_forecast[$day_num]['conditions'] = $forecastday->{'conditions'};
    $array_forecast[$day_num]['mm'] = $forecastday->{'qpf_allday'}->{'mm'};
    $array_forecast[$day_num]['pop'] = $forecastday->{'pop'} / 100;
    $day_num++;
}

//данные день-ночь
$forecasts = $parsed_json->{'forecast'}->{'txt_forecast'}->{'forecastday'};
foreach ($forecasts as $forecast) {
    $period = $forecast->{'period'};
    if ($period % 2) {
        $object_num = intval(($period - 1) / 2);
        $array_forecast[$object_num]['text_night'] = $forecast->{'fcttext_metric'};
        $array_forecast[$object_num]['icon_url_night'] = updateIconUrl($forecast->{'icon_url'});
    } else {
        $object_num = intval(($period) / 2);
        $array_forecast[$object_num]['text_day'] = $forecast->{'fcttext_metric'};
        $array_forecast[$object_num]['icon_url_day'] = updateIconUrl($forecast->{'icon_url'});
    }
}

array_pop($array_forecast);
$conditions_forecast = "<div class='row'>";
$array_forecast[0]['weekday']="Сегодня";
$array_forecast[1]['weekday']="Завтра";

foreach ($array_forecast as $forecast_object) {
    if ((int)$forecast_object['temp_high']> 0 )  $forecast_object_high = "+".$forecast_object['temp_high'] ; else $forecast_object_high = $forecast_object['temp_high'];
    if ((int)$forecast_object['temp_low']> 0 )  $forecast_object_low = "+".$forecast_object['temp_low'] ; else $forecast_object_low = $forecast_object['temp_low'];
    $short_conditions = $forecast_object['conditions'];
    $icon_url_day = str_replace('http:','',$forecast_object['icon_url_day']);
    $conditions_forecast .= " 
    <div class='day'>
        <div class='text-center'>
            <span class='weekday'>${forecast_object['weekday']}
                <span class='weekday_is-night hidden not-really-$hide_first_day_weather_on_evening'>ночью</span>
            </span>
            <span class='date'>${forecast_object['day']}</span>
        </div>
        <div class='weather-icon-wrap'>
            <img class='weather-icon' src='$icon_url_day' alt='Значок погоды'>
        </div>
        <div class='temps'>
            <span class='high'>$forecast_object_high</span>
            <span class='split'></span>
            <span class='low'>$forecast_object_low</span> ℃
        </div>
        <p class='conditions'>$short_conditions</p>";
        if ($forecast_object['mm'] > 0 || $forecast_object['pop'] > 0)
            $conditions_forecast .= "<div title='Вероятность дождя: ". $forecast_object['pop']*100 ."%\n" .
                "Выпадет ". $forecast_object['mm'] ." мм осадков. ' class='pop' " ."style='background-color: rgba(41, 182, 246, ${forecast_object['pop']});'>
                <span class='drop-icon'></span><strong>${forecast_object['mm']}</strong> мм</div>";
        else $conditions_forecast .= "<div title='Осадков не ожидается' class='pop pop-dry'>Сухо</div>";

        $conditions_forecast .= "</div>";
    }


$conditions_forecast .= "</div><h6 class='text-center'><a href='https://pogoda.yandex.ru/krasnoufimsk/details'>
П<span class='hidden-xs'>одробный п</span>рогноз погоды на 10 дней <i class='fa fa-arrow-right'></i></a></h6>";

//echo $conditions_forecast;
//var_dump($array_forecast);

// TODO: Обрабатывать ошибки сервера Weather Underground
if ( false /*is_nan($temp_c) || $temp_c === null || $description == "" || $icon == "" */ ) {
    header("Status: 503 Internal server error");
    prettyNotice('Weatherunderground (forecast) is offline, using Yandex', "danger");
    $forecast = '<a class="ya-weather-forecast" href="https://pogoda.yandex.ru/krasnoufimsk/details" target="_blank">
                    <img alt="Погода" src="http://info.weather.yandex.net/krasnoufimsk/2_white.ru.png?domain=ru">
                 </a>';
} else {
    $forecast = $conditions_forecast;
}

if (file_put_contents("forecast.html", $forecast)) {
    prettyNotice("Сохранён файл <a href='/weather/forecast.html'>forecast.html</a>");
} else {
    header("Status: 503 Internal server error");
    prettyNotice("Не удалось сохранить <a href='/weather/forecast.html'>forecast.html</a>", "danger");
}
echo  "lable pogoda= ".$icon_url_day;
?>

<div id='header'>
    <header class="row row_header hidden-print" id="header" data-version="1" xmlns="http://www.w3.org/1999/html">
        <div class="header-logo-col col-xs-4 col-sm-4 col-md-5 col-lg-5">
            <div itemscope itemtype="http://schema.org/Organization" class="header-logo">
                <a itemprop="url" href="/" title="На главную страницу" class="logo-container">
                    <img itemprop="logo" alt="Красноуфимск онлайн" src="https://ksk1.ru/img/logo-mobile-ksk.svg" class="visible-xs">
                    <img alt="Красноуфимск онлайн" style="position: absolute" src="https://ksk1.ru/img/logo-base.svg" class="hidden-xs">
                    <div id="sublogo"></div>
                </a>
            </div>
            <div class="weather-temp-sm triggers-weather hidden-lg hidden-xs">
                <?= $conditions ?>
            </div>
        </div>

        <div class="weather-block-col triggers-weather visible-lg col-lg-1">
            <?= $conditions ?>
        </div>
    </header>
    <div id='navpanel-info' class='navpanel navpanel-info row active'>
        <div class="col-xs-12 col-sm-7 subpanel">
            <h4 class="weather-panel-heading">Погода в Красноуфимске</h4>
            <div class="col-xs-12 weather-forecast-panel clearfix" id="weather-panel" style="padding: 0;">
                <?= $forecast ?>
            </div>
        </div>
    </div>
</div>


</div>
</body>
</html>


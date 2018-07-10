<?php
// Запись логов
function setLog($message) {
    $log_file_name = 'logs.txt';

    if(file_exists($log_file_name)) {
        $log = array_diff(explode("\r\n", file_get_contents($log_file_name)), array(''));
    }

    $log[] = date("m.d.Y-H:i:s").' | '.$message;

    if(file_put_contents($log_file_name, implode("\r\n", $log))) {
        return true;
    } else {
        return false;
    }
}

// Загрузка фото на диск
function DownloadImages($url, $filename){
    $ch = curl_init($url);
    $fp = fopen($filename, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}

// CURL
function getPOST($url, $post) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

// Создание и отправка запроса к API VK
function getApiMethod($method_name, $params) {
    global $access_token;
    global $api_version;

    if (!array_key_exists('access_token', $params) && !is_null($access_token)) {
        $params['access_token'] = $access_token;
    }

    if (!array_key_exists('v', $params) && !is_null($api_version)) {
        $params['v'] = $api_version;
    }
    
    ksort($params);
    
    return(getPOST('https://api.vk.com/method/'.$method_name, $params));
}

// Загрузка обложки по времени суток
function timeToDayBg(){
    global $show_time_of_day;
    global $image_bg;

	$clock = date("H");
    if($show_time_of_day) {
        return $image_bg['background'];
    }
	
}

// Круглые аватарки
function RoundingOff($_imagick, $width, $height) {
    $_imagick->adaptiveResizeImage($width, $height, 100);
    $_imagick->setImageFormat('png');
        
    $_imagick->roundCornersImage(
        90, 90, 0, 0, 0
    );
}

?>
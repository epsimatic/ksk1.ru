<?php
// Разрешение на отображение ошибок на экране
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once('setup.php');
require_once('function.php');

header('Content-type: text/html; charset=utf-8');

echo '<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">';
echo '<link href="style.css" rel="stylesheet">';

// Получим текущую дату
$date_today = date('Ymd');

// Вывод месяца на русском
$monthes = array(
    1 => 'Января', 2 => 'Февраля', 3 => 'Марта', 4 => 'Апреля',
    5 => 'Мая', 6 => 'Июня', 7 => 'Июля', 8 => 'Августа',
    9 => 'Сентября', 10 => 'Октября', 11 => 'Ноября', 12 => 'Декабря'
);

// Вывод дня недели на русском
$days = array(
    'Воскресенье', 'Понедельник', 'Вторник', 'Среда',
    'Четверг', 'Пятница', 'Суббота'
);

require_once('groups_getMembers.php');

// --- РИСОВАНИЕ ---------------------
setLog('Создаем обложку');

$draw = new ImagickDraw(); 

if(file_exists(BASEPATH.timeToDayBg())) {
    $bg = new Imagick(BASEPATH.timeToDayBg());
} else {
    setLog('ОШИБКА! Не найден '.BASEPATH.timeToDayBg().' Загрузите файл либо отключите $show_time_of_day в config.php');
    print_r('ОШИБКА! Не найдена обложка, смотрите logs.txt');
    exit;
}

$draw->setFont(BASEPATH."/font/".$font);
$draw->setTextAlignment(Imagick::ALIGN_CENTER);

//ПОСЛЕДНИЕ ПОДПИСЧИКИ
if($view_last_subscriber) {
    $file_name_1 = BASEPATH.'cover/last_subscriber_1.jpg';
	$file_name_2 = BASEPATH.'cover/last_subscriber_2.jpg';
	$file_name_3 = BASEPATH.'cover/last_subscriber_3.jpg';

	//ПОДПИСЧИК #1
    if(file_exists($file_name_1) && $view_last_subscriber) {
        $last_subscriber_photo_1 = new Imagick($file_name_1);
        if($roundingOff==true) {
            RoundingOff($last_subscriber_photo_1, $last_subscriber_width,$last_subscriber_height);
        }

        $draw->setFontSize($last_subscriber_font_size);
        $draw->setFillColor("rgb(".$last_subscriber_font_color.")");

        $bg->compositeImage($last_subscriber_photo_1, Imagick::COMPOSITE_DEFAULT, $last_subscriber_photo_1_x, $last_subscriber_photo_1_y);
        $bg->annotateImage($draw, $last_subscriber_1_text_x, $last_subscriber_1_text_y, 0, mb_strtoupper($last_subscriber_firstname_1.' '.$last_subscriber_lastname_1, 'UTF-8'));
    }
	
	//ПОДПИСЧИК #2
	if(file_exists($file_name_2) && $view_last_subscriber) {
        $last_subscriber_photo_2 = new Imagick($file_name_2);
        if($roundingOff==true) {
            RoundingOff($last_subscriber_photo_2, $last_subscriber_width,$last_subscriber_height);
        }

        $draw->setFontSize($last_subscriber_font_size);
        $draw->setFillColor("rgb(".$last_subscriber_font_color.")");

        $bg->compositeImage($last_subscriber_photo_2, Imagick::COMPOSITE_DEFAULT, $last_subscriber_photo_2_x, $last_subscriber_photo_2_y);
        $bg->annotateImage($draw, $last_subscriber_2_text_x, $last_subscriber_2_text_y, 0, mb_strtoupper($last_subscriber_firstname_2.' '.$last_subscriber_lastname_2, 'UTF-8'));
    }
	
	//ПОДПИСЧИК #3
	if(file_exists($file_name_3) && $view_last_subscriber) {
        $last_subscriber_photo_3 = new Imagick($file_name_3);
        if($roundingOff==true) {
            RoundingOff($last_subscriber_photo_3, $last_subscriber_width,$last_subscriber_height);
        }

        $draw->setFontSize($last_subscriber_font_size);
        $draw->setFillColor("rgb(".$last_subscriber_font_color.")");

        $bg->compositeImage($last_subscriber_photo_3, Imagick::COMPOSITE_DEFAULT, $last_subscriber_photo_3_x, $last_subscriber_photo_3_y);
        $bg->annotateImage($draw, $last_subscriber_3_text_x, $last_subscriber_3_text_y, 0, mb_strtoupper($last_subscriber_firstname_3.' '.$last_subscriber_lastname_3, 'UTF-8'));
    }
}

// ВЫВОДИМ ДЕНЬ НЕДЕЛИ
if($view_today){
	$draw->setFont(BASEPATH."/font/".$font_date);
	$draw->setTextAlignment(Imagick::ALIGN_LEFT);
    $draw->setFontSize($today_font_size);
    $draw->setFillColor("rgb(".$today_font_color.")");
    $bg->annotateImage($draw, $today_text_pixel_x, $today_text_pixel_y, 0, mb_strtoupper($days[(date('w'))], 'UTF-8'));
}

// ВЫВОДИМ ДАТУ
if($view_date){
	$draw->setFont(BASEPATH."/font/".$font_date);
	$draw->setTextAlignment(Imagick::ALIGN_LEFT);
    $draw->setFontSize($date_font_size);
    $draw->setFillColor("rgb(".$date_font_color.")");
    $bg->annotateImage($draw, $date_text_pixel_x, $date_text_pixel_y, 0, mb_strtoupper(date('d ') . $monthes[(date('n'))] . date(' Y'), 'UTF-8'));
}

// ВЫВОДИМ ВРЕМЯ
if($view_clock){
	$draw->setFont(BASEPATH."/font/".$font_clock);
	$draw->setTextAlignment(Imagick::ALIGN_LEFT);
    $draw->setFontSize($clock_font_size);
    $draw->setFillColor("rgb(".$clock_font_color.")");
    $bg->annotateImage($draw, $clock_text_pixel_x, $clock_text_pixel_y, 0, mb_strtoupper(date('H:i'), 'UTF-8'));
}

// СОХРАНЯЕМ ФАЙЛ
$bg->setImageFormat("png");
$bg->writeImage($output_cover);

// --- ЗАГРУЗКА НА СЕРВЕР ------------

$getUrl = getApiMethod('photos.getOwnerCoverPhotoUploadServer', array(
    'group_id' => $group_id,
    'crop_x2' => '1590'
));
setLog('Адресс сервера... '.$getUrl);

if($getUrl) {
    $getUrl = json_decode($getUrl, true);

    $url = $getUrl['response']['upload_url'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('photo' => new CURLFile($output_cover, 'image/jpeg', 'image0')));
    $upload = curl_exec( $ch );
    curl_close( $ch );

    if($upload) {
        $upload = json_decode($upload, true);

        $getUrl = getApiMethod('photos.saveOwnerCoverPhoto', array(
            'hash' => $upload['hash'],
            'photo' => $upload['photo'],
        ));
        
        setLog('Загружаем обложку '.$getUrl);

        if(stripos($getUrl, 'response":{"images":[{')) {
            print_r('<p>Динамическая обложка успешно загружена в <a href="https://vk.com/club' . $group_id . '" target="_blank" rel="noopener noreferrer">группу</a></p>' . PHP_EOL);
            echo '<p><img src="'.'cover/output.png'.'" width="795" height="200"></p>';
            setLog('Загружаем обложку в https://vk.com/club'.$group_id);
        } else {
            print_r('Ошибка загрузки! '.$getUrl);
            setLog('Ошибка загрузки! '.$getUrl);
        }
    }
}

?>
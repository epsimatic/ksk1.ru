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

if($show_last_subscribe) {
    sleep(5);
    // Теперь найдем последнего подписчика
    $last_subscribe = getApiMethod('groups.getMembers', array(
        'group_id' => $group_id,
        'sort' => 'time_desc',
        'count' => '1',
        'fields' => 'photo_200',
        'access_token' => $access_token
    ));

    setLog('Ответ сервера #5 '.$last_subscribe);

    if($last_subscribe) {
        $last_subscribe = json_decode($last_subscribe, true);

        $members_count = $last_subscribe['response']['count'];
        $last_subscribe_firstname = $last_subscribe['response']['items'][0]['first_name'];
        $last_subscribe_lastname = $last_subscribe['response']['items'][0]['last_name'];
        $last_subscribe_photo = $last_subscribe['response']['items'][0]['photo_200'];
       // echo 'Последний подписчик '.$last_subscribe_firstname ;
      //  setLog('Получаю последнего вступившего в группу: '.$last_subscribe_firstname.' '.$last_subscribe_lastname);
      //  echo '<p>*** Последний подписчик '.$last_subscribe_firstname.' '.$last_subscribe_lastname.'</p></br>';
        // Скачиваем фото
        if(!empty($last_subscribe_firstname) && !empty($last_subscribe_lastname) && !empty($last_subscribe_photo)){
            DownloadImages($last_subscribe_photo, 'cover/last_subscribe.jpg');
        }

    }
}

//$date_today = date('Ymd',strtotime("yesterday"));
$date_today = date('Ymd');
if($show_top_comments) {
    setLog('Получаю посты группы');
    // Получим посты со стены
    // больше 100 постов получать нет смысла, так как в вк ограничение
    // разрешено постить не больше 50 постов в сутки.
    $wall_get = getApiMethod('wall.get', array(
        'owner_id' => '-'.$group_id,
        'count' => '100'
    ));

    setLog('Ответ сервера #1 '.$wall_get);

    if($wall_get) {
        $wall_get = json_decode($wall_get, true);

        //checkApiError($wall_get);

        $countlike = array();
        $countcomments = array();

        foreach($wall_get['response']['items'] as $wall) {

            // Получим кол-во комментариев к посту
            $count = $wall['comments']['count'];
            $offset = 0;

            if($count > 0) {
                // Получим все комментарии, так как их может быть больше 100.
                while($offset < $count) {
                    setLog('Получаю кол-во комментариев к посту '.$wall['id']);
                    // Отправим запрос на получение комментариев
                    $comments_get = getApiMethod('wall.getComments', array(
                        'owner_id' => '-'.$group_id,
                        'post_id' => $wall['id'],
                        'need_likes' => '1',
                        'count' => '100',
                        'offset' => $offset
                    ));

                    if($comments_get) {
                        $comments_get = json_decode($comments_get, true);

                        foreach($comments_get['response']['items'] as $comments) {

                           if($date_today == date('Ymd', $comments['date'])) {
                                // В двух словах мы заносим данные в массив, суммируя их
                                if(!isset($countcomments[$comments['from_id']]) and !isset($countlike[$comments['from_id']])) {
                                    $countcomments[$comments['from_id']] = 1;
                                    $countlike[$comments['from_id']] = $comments['likes']['count'];
                                } else {
                                    $countcomments[$comments['from_id']]++;
                                    $countlike[$comments['from_id']] += $comments['likes']['count'];
                                }
                                //var_dump($comments);
                            }

                        }
                    }

                    if($offset<$count)
                        $offset = $offset + 100;

                }
            }

        }
    }
}

if($show_top_comments) {
    $day_comment_top = 0;
    if(count($countcomments) > 0) {
        // Теперь найдем кто суммарно написал больше всех комментариев
        $value = max($countcomments);
        $day_comment_top = array_search($value, $countcomments);
        setLog('COUNT COMMENT: '.$countcomments[$day_comment_top]);
        setLog('Получаю ID кто суммарно написал больше всех комментариев '.$day_comment_top);


        sleep(5);

        if($day_comment_top > 0) {
            $user_top_comment = getApiMethod('users.get', array(
                'user_ids' => $day_comment_top,
                'fields' => 'photo_200'
            ));

            setLog('Ответ сервера #4 '.$user_top_comment);

            if($user_top_comment) {
                $user_top_comment = json_decode($user_top_comment, true);

                $top_comment_name = $user_top_comment['response'][0]['first_name'];
                $top_comment_lastname = $user_top_comment['response'][0]['last_name'];
                $top_comment_photo = $user_top_comment['response'][0]['photo_200'];

                setLog('И.Ф Комменты: '.$top_comment_name.' '.$top_comment_lastname);
              //  echo '<p>*** Больше всех сегодня комментариев написал: '.$top_comment_name.' '.$top_comment_lastname.' - '.$countcomments[$day_comment_top].' шт.</p></br>';
                // Скачиваем фото
                if(!empty($top_comment_photo)){
                    DownloadImages($top_comment_photo, 'cover/top_comments.jpg');
                }
            }
        }
    }
}


//
if($view_last_subscriber) {
    $file_name_1 = BASEPATH.'cover/last_subscribe.jpg';
	$file_name_2 = BASEPATH.'cover/top_comments.jpg';
	//$file_name_3 = file_get_contents('https://ksk1.ru/weather/conditions.html');

	//последний ПОДПИСЧИК
    if($view_last_subscriber) {
        $last_subscriber_photo_1 = new Imagick($file_name_1);
        if($roundingOff==true) {
            RoundingOff($last_subscriber_photo_1, $last_subscriber_width,$last_subscriber_height);
        }

        $draw->setFontSize($last_subscriber_font_size);
        $draw->setFillColor("rgb(".$last_subscriber_font_color.")");

        $bg->compositeImage($last_subscriber_photo_1, Imagick::COMPOSITE_DEFAULT, $last_subscriber_photo_1_x, $last_subscriber_photo_1_y);
        $bg->annotateImage($draw, $last_subscriber_1_text_x, $last_subscriber_1_text_y, 0, mb_strtoupper($last_subscribe_firstname.' '.$last_subscribe_lastname, 'UTF-8'));
    }
	
	//ПОДПИСЧИК с наибольшими комментариями
	if(file_exists($file_name_2) && $view_last_subscriber) {
        $last_subscriber_photo_2 = new Imagick($file_name_2);
        if($roundingOff==true) {
            RoundingOff($last_subscriber_photo_2, $last_subscriber_width,$last_subscriber_height);
        }

        $draw->setFontSize($last_subscriber_font_size);
        $draw->setFillColor("rgb(".$last_subscriber_font_color.")");

        $bg->compositeImage($last_subscriber_photo_2, Imagick::COMPOSITE_DEFAULT, $last_subscriber_photo_2_x, $last_subscriber_photo_2_y);
        $bg->annotateImage($draw, $last_subscriber_2_text_x, $last_subscriber_2_text_y, 0, $file_name_3);
    }
	

}

// погоду получаем
$ch = curl_init( "https://api.weather.yandex.ru/v1/informers?lat=56.618007&lon=57.779208");
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
curl_setopt( $ch, CURLOPT_TIMEOUT, 3 );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-Yandex-API-Key: 90b3db9b-dcf5-4b5e-b8e0-8c14d83620a9'));

$response = json_decode(curl_exec( $ch ));
$error_code = curl_errno($ch);
curl_close($ch);
// выводим иконку

$file_icon='https://yastatic.net/weather/i/icons/blueye/color/svg/'.$response->fact->icon.'.svg';
DownloadImages($file_icon, 'cover/icon.svg');
/*$usmap = 'https://scriptvk/podslushano/cover/icon.svg';
$im = new Imagick();
$svg = file_get_contents($usmap);




$im->readImageBlob($svg);

//png settings
$im->setImageFormat("png24");
$im->resizeImage(720, 445, imagick::FILTER_LANCZOS, 1);
//Optional, if you need to resize

//jpeg
$im->setImageFormat("jpeg");
$im->adaptiveResizeImage(720, 445);
//Optional, if you need to resize

$im->writeImage('https://scriptvk/podslushano/cover/icon.jpg');
//(or .jpg)
$im->clear();
$im->destroy();*/

$file_name_3 = 'https://ksk1.ru/scriptvk/podslushano/cover/ksk-tv-ok.jpg';
$icon_photo_3 = new Imagick($file_name_3);
if(file_exists($file_name_3) && $view_last_subscriber) {
    $last_subscriber_photo_3 = new Imagick($file_name_3);

    $draw->setFontSize($last_subscriber_font_size);
    $draw->setFillColor("rgb(".$last_subscriber_font_color.")");

    $bg->compositeImage($last_subscriber_photo_3, Imagick::COMPOSITE_DEFAULT, $last_subscriber_photo_3_x, $last_subscriber_photo_3_y);
    $bg->annotateImage($draw, $last_subscriber_3_text_x, $last_subscriber_3_text_y, 0, $file_name_3);
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
date_default_timezone_set("Asia/Yekaterinburg");
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
<?php

// Токен
$access_token = '49c94980ab18fa3ffe6175dca3d26b3e3a8125df690b7e95feb3af7d4444f1bd79abfbba46894e939cb13';
// ID группы
$group_id = '93293773';
// Версия API
$api_version = '5.64';

//[true - разрешить false - запретить]
$view_last_subscriber = true; // Последний подписчик
$view_today = false; // День недели
$view_date = true; // Число
$view_clock = true; // Часы

/* ------------------------ ТИПОГРАФИКА (папка font) -------------------- */
$font = "MyriadPro-Cond.otf";
$font_clock = "MyriadPro-Cond.otf";
$font_date = "MyriadPro-Cond.otf";

// Подписчики
$last_subscriber_font_size = 26;
//$last_subscriber_font_color = '255,255,255';
$last_subscriber_font_color = '0,0,0';

// День недели
$today_font_size = 55;
$today_font_color = '255,255,255';

// Число
$date_font_size = 30;
$date_font_color = '255,255,255';

// Часы
$clock_font_size = 30;
$clock_font_color = '255,255,255';

/* ------------------------ ПОСЛЕДНИЙ ПОДПИСАВШИЙСЯ --------------------- */
// Показывать последнего подписчика [true - показывать false - нет]
$show_last_subscribe = true;
$show_top_comments = true;
// Необходимое кол-во подписчиков [1, 2 или 3]
$last_subscriber_count = '1';

// Ширина аватарки
$last_subscriber_width = 100; //
// Высота аватарки
$last_subscriber_height = 100; //
// Аватарки [true - круглые false - квадратные]
$roundingOff = true;

/* ----- subscriber_1 ----- */
// положение аватарки
$last_subscriber_photo_1_x = 860;   // 816
$last_subscriber_photo_1_y = 253;  //  117
// Координаты имени и фамилии
$last_subscriber_1_text_x = 900;  // 911
$last_subscriber_1_text_y = 380;  //  342

/* ----- subscriber_2 ----- */
// положение аватарки
$last_subscriber_photo_2_x = 630;
$last_subscriber_photo_2_y = 253;
// Координаты имени и фамилии
$last_subscriber_2_text_x = 650;
$last_subscriber_2_text_y = 380;

/* ----- subscriber_3 ----- */
// положение аватарки
$last_subscriber_photo_3_x = 300;
$last_subscriber_photo_3_y = 117;
// Координаты имени и фамилии
$last_subscriber_3_text_x = 1421;
$last_subscriber_3_text_y = 342;

/* ------------------------ НАСТРОЙКА TODAY ------------------------ */

// Координаты
$today_text_pixel_x = 53;
$today_text_pixel_y = 267;

/* ------------------------ НАСТРОЙКА ДАТЫ ------------------------ */

// Координаты
$date_text_pixel_x = 46;
$date_text_pixel_y = 384;

/* ------------------------ НАСТРОЙКА ЧАСОВ ------------------------ */

// Координаты
$clock_text_pixel_x = 46;
$clock_text_pixel_y = 343;

/* ------------------------ КОНСТАНТА [НЕ ИЗМЕНЯТЬ] -------------------- */

// Директория
define('BASEPATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
// Часовой пояс
date_default_timezone_set('Europe/Moscow');
// Путь к исходной обложке [размер 1590 x 400px]
$show_time_of_day = true; 
$image_bg = array(
    'background' => 'cover/cover_bg2.jpg',
);
// Путь к готовой обложке 
$output_cover = BASEPATH.'cover/output.png';
// Начало сессии
session_start();
?>
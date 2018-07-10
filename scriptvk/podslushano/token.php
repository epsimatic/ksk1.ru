<?php
header('Content-type: text/html; charset=utf-8');
echo '<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">';
echo '<link href="style.css" rel="stylesheet">';

$params = [
	'client_id' => '6625880',
	'redirect_uri' => 'https://oauth.vk.com/blank.html',
	'response_type' => 'token',
	'scope' => 'friends,photos,audio,video,docs,notes,pages,status,offers,questions,wall,groups,messages,notifications,stats,ads,market,offline',
];

$url = 'https://oauth.vk.com/authorize?' . http_build_query($params);

echo '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">Получить токен</a>' . PHP_EOL;

?>
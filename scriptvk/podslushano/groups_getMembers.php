<?php
// Находим последнего подписчика
if($view_last_subscriber) {
    
    $last_subscriber = getApiMethod('groups.getMembers', array(
                'group_id' => $group_id,
                'sort' => 'time_desc',
                'count' => $last_subscriber_count,
                'fields' => 'photo_200',
                'access_token' => $access_token
            ));

    setLog('Массив last_subscriber: '.$last_subscriber);

    if($last_subscriber) {
        $last_subscriber = json_decode($last_subscriber, true);
		
		foreach($last_subscriber['response']['items'] as $user ) {
			$last_users[] = $user; // добавляем юзера к юзерам
		}
		
		foreach ($last_users as $k => $last_user){
			// Скачиваем фото
			DownloadImages($last_user['photo_200'], 'cover/last_subscriber_'.($k+1).'.jpg');
		}

		$last_subscriber_firstname_1 = $last_users[0]['first_name'];
        $last_subscriber_lastname_1 = $last_users[0]['last_name'];
        $last_subscriber_photo_1 = $last_users[0]['photo_200'];
		
		$last_subscriber_firstname_2 = $last_users[1]['first_name'];
        $last_subscriber_lastname_2 = $last_users[1]['last_name'];
        $last_subscriber_photo_2 = $last_users[1]['photo_200'];
		
		$last_subscriber_firstname_3 = $last_users[2]['first_name'];
        $last_subscriber_lastname_3 = $last_users[2]['last_name'];
        $last_subscriber_photo_3 = $last_users[2]['photo_200'];
    }
}

?>
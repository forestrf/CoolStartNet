<?php

if(!isset($_POST['widgetID']) || !isset($_POST['action']) || !isset($_POST['token'])){
	exit;
}



/*
Actions:
1 => Add widget to the user
2 => Remove widget from the user
3 => Manually set the widget version
4 => Automatically set the widget version
*/


// Check referer

$possibles_referrer = array(
	'widgetsuser.php',
	'widgetsuserversion.php'
);

foreach($possibles_referrer as $referer_temp){
	foreach(array('http', 'https') as $protocol){
		if(strpos($_SERVER['HTTP_REFERER'], $protocol.'://'.WEB_PATH.$referer_temp) === 0){
			// Valid referer
			
			// Check token
			$token_objetivo = hash_ipa($_SESSION['user']['RND'], $_POST['widgetID'], PASSWORD_TOKEN_IPA);
			if($_POST['token'] === $token_objetivo){
				
				switch($_POST['action']){
					case '1':
						$db->remove_using_widget_user($_POST['widgetID']);
					break;
					case '2':
						$db->add_using_widget_user($_POST['widgetID']);
					break;
					case '3':
						if(isset($_POST['widgetVersion']) && isInteger($_POST['widgetVersion']) && $_POST['widgetVersion'] >= 0){
							$db->set_using_widget_version_user($_POST['widgetID'], $_POST['widgetVersion']);
						}
					break;
					case '4':
						$db->set_using_widget_version_autoupdate_user($_POST['widgetID']);
					break;
				}
			}
			break 2;
		}
	}
}
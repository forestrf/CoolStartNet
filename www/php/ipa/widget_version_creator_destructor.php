<?php

if(!isset($_POST['widgetID']) || !isset($_POST['action']) || !isset($_POST['token'])){
	exit;
}



/*
Actions:
1 => Create
2 => Delete
*/


// Check referer

$possibles_referrer = array(
	'widgetedit'
);

foreach($possibles_referrer as $referer_temp){
	foreach(array('http', 'https') as $protocol){
		if(strpos($_SERVER['HTTP_REFERER'], $protocol.'://'.WEB_PATH.$referer_temp) === 0){
			// Valid referer
			
			// check widget ID
			if(isset($_POST['widgetID']) && isInteger($_POST['widgetID']) && $_POST['widgetID'] >= 0){
				// Check token
				if($_POST['token'] === hash_ipa(G::$SESSION->get_user_random(), $_POST['widgetID'], PASSWORD_TOKEN_IPA)){
					switch($_POST['action']){
						case '1':
							$db->create_widget_version($_POST['widgetID']);
						break;
						case '2':
							if(isset($_POST['widgetVersion']) && isInteger($_POST['widgetVersion']) && $_POST['widgetVersion'] >= 0){
								$db->delete_private_widget_version($_POST['widgetID'], $_POST['widgetVersion']);
							}
						break;
					}
				}
			}
			break 2;
		}
	}
}
<?php

if(!isset($_POST['name']) && !isset($_POST['widgetID'])){
	exit;
}



/*
Actions:
1 => Create
2 => Delete
*/


// Check referer

$possibles_referrer = array(
	'widgetlist'
);

foreach($possibles_referrer as $referer_temp){
	foreach(array('http', 'https') as $protocol){
		if(strpos($_SERVER['HTTP_REFERER'], $protocol.'://'.WEB_PATH.$referer_temp) === 0){
			// Valid referer
			
			switch($_POST['action']){
				case '1':
					// Check token
					if($_POST['token'] === hash_ipa($_SESSION['user']['RND'], -1, PASSWORD_TOKEN_IPA)){
						// Check name
						if(isset($_POST['name']) && $_POST['name'] !== '' && preg_match('@[a-zA-Z0-9 ]{1,30}@', $_POST['name'])){
							$db->create_widget($_POST['name']);
						}
					}
				break;
				case '2':
					// check widget ID
					if(isset($_POST['widgetID']) && isInteger($_POST['widgetID']) && $_POST['widgetID'] >= 0){
						// Check token
						if($_POST['token'] === hash_ipa($_SESSION['user']['RND'], $_POST['widgetID'], PASSWORD_TOKEN_IPA)){
							$db->delete_widget($_POST['widgetID']);
						}
					}
				break;
			}
			break 2;
		}
	}
}
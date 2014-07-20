<?php

if(!isset($_POST['widgetID']) || !isset($_POST['action']) || !isset($_POST['token'])){
	exit;
}

session_start();
if(!isset($_SESSION['user'])){
	exit;
}


require_once __DIR__.'/../config.php';
require_once __DIR__.'/../functions/generic.php';
require_once __DIR__.'/../class/DB.php';

$db = new DB();


/*
Actions:
1 => Select a version as the default version
2 => Make a public version hidden
3 => Make a public version visible
4 => Publicate a version
5 => Hide all the versions
*/


// Check referer

$possibles_referrer = array(
	'widgetedit.php',
	'widgetlist.php'
);

foreach($possibles_referrer as $referer_temp){
	foreach(array('http', 'https') as $protocol){
		if(strpos($_SERVER['HTTP_REFERER'], $protocol.'://'.WEB_PATH.$referer_temp) === 0){
			// Valid referer
			
			// check widget ID
			if(isset($_POST['widgetID']) && isInteger($_POST['widgetID']) && $_POST['widgetID'] >= 0){
				// Check token
				if($_POST['token'] === hash_ipa($_SESSION['user']['RND'], $_POST['widgetID'], PASSWORD_TOKEN_IPA)){
					if($_POST['action'] === '5'){
						$db->hide_all_widget_versions($_POST['widgetID']);
					}
					else if(isset($_POST['widgetVersion']) && isInteger($_POST['widgetVersion']) && $_POST['widgetVersion'] >= 0){
						switch($_POST['action']){
							case '1':
								$db->set_widget_default_version($_POST['widgetID'], $_POST['widgetVersion']);
							break;
							case '2':
								$db->set_widget_version_visibility($_POST['widgetID'], $_POST['widgetVersion'], false);
							break;
							case '3':
								$db->set_widget_version_visibility($_POST['widgetID'], $_POST['widgetVersion'], true);
							break;
							case '4':
								// If the version has a file called "main.js" it can be made public
								if($db->can_publicate_widget_version_check($_POST['widgetID'], $_POST['widgetVersion'])){
									$db->publicate_widget_version($_POST['widgetID'], $_POST['widgetVersion']);
								}
							break;
							case '6':
								if(isset($_POST['comment'])){
									if(strlen($_POST['comment']) > WIDGET_VERSION_COMMENT_MAX_LENGTH){
										$comment = substr($comment, 0, WIDGET_VERSION_COMMENT_MAX_LENGTH);
									}
									else{
										$comment = $_POST['comment'];
									}
									$db->set_widget_comment($_POST['widgetID'], $_POST['widgetVersion'], $_POST['comment']);
								}
							break;
						}
					}
				}
			}
			break 2;
		}
	}
}
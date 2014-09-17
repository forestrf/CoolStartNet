<?php

header('Content-Type: text/html; charset=UTF-8');

require_once 'php/functions/generic.php';
insert_nocache_headers();

if(!isset($_POST['switch'])){
	exit;
}

require_once __DIR__.'/php/functions/generic.php';
$db = open_db_session();

user_check_access();

// This API must be only called by "me" and not from widgets. Only for configurations of the web.
// To prevent it, must be send a token and must be an expected referer.
// The referer must be the page(s) that can configure the value.
// The token is a md5 generated using the variable to be modified, a password from config.php and the RND of the user. function hash_ipa().

/*
1 => Make the user use or not a widget
2 => Create or delete widgets
3 => Create or delete a widget version
4 => Edit a widget version (upload and edit files)
5 => Manage widget versions
*/

switch($_POST['switch']){
	case '1':
		require 'php/ipa/user_widget_list.php';
	break;
	case '2':
		require 'php/ipa/widget_creator_destructor.php';
	break;
	case '3':
		require 'php/ipa/widget_version_creator_destructor.php';
	break;
	case '4':
		require 'php/ipa/edit_widget_version.php';
	break;
	case '5':
		require 'php/ipa/manage_widget_versions.php';
	break;
}


if(isset($_POST['goback']) && $_POST['goback'] === '1'){
	header('HTTP/1.1 302 Moved Temporarily');
	header('Location: '.$_SERVER['HTTP_REFERER']); 
}
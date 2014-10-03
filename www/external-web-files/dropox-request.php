<?php
require_once __DIR__.'/../php/config.php';
require_once __DIR__.'/../php/functions/generic.php';
require_once __DIR__.'/dropbox-functions.php';

$db = open_db_session();
user_check_access();

// Check if the user has a key already
if(isset($_SESSION['user']['dropbox_accessToken']) && $_SESSION['user']['dropbox_accessToken'][5]){
	print_r($_SESSION);
	exit;
} else {
	$authorizeUrl = getWebAuth()->start();
	header("Location: {$authorizeUrl}");	
}

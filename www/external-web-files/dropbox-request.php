<?php
require_once __DIR__.'/../php/config.php';
require_once __DIR__.'/../php/functions/generic.php';
require_once __DIR__.'/dropbox-functions.php';

$db = open_db_session();
user_check_access();

// Check if the user has a key already
$tokens = $db->getAllAccessToken();
if($tokens && isset($tokens['dropbox_accessToken']) && isset($tokens['dropbox_accessToken'][1])){
	echo 'the user has dropbox';
} else {
	$authorizeUrl = getWebAuth()->start();
	header("Location: {$authorizeUrl}");	
}

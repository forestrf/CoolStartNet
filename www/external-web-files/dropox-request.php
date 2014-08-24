<?php
require_once '../php/config.php';
require_once 'dropbox-functions.php';

session_start();
if(!isset($_SESSION['user'])){
	exit;
}

// Check if the user has a key already
if(isset($_SESSION['user']['dropbox_accessToken']) && $_SESSION['user']['dropbox_accessToken'][5]){
	print_r($_SESSION);
	exit;
} else {
	$authorizeUrl = getWebAuth()->start();
	header("Location: {$authorizeUrl}");	
}

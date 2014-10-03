<?php

require_once __DIR__.'/../php/config.php';

# Include the Dropbox SDK libraries
require_once __DIR__.'/../php/lib/Dropbox/autoload.php';
use \Dropbox as dbx;

function getWebAuth(){
	$appInfo          = new dbx\AppInfo(DROPBOX_KEY, DROPBOX_SECRET);
	$clientIdentifier = DROPBOX_APP_NAME;
	$redirectUri      = 'https://'.WEB_PATH.'external-web-files/dropbox-response.php';
	$csrfTokenStore   = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
	return new dbx\WebAuth($appInfo, $clientIdentifier, $redirectUri, $csrfTokenStore);
}
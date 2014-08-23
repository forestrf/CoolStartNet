<?php

require_once '../php/config.php';

# Include the Dropbox SDK libraries
require_once '../php/lib/Dropbox/autoload.php';
use \Dropbox as dbx;

function getWebAuth(){
	$appInfo          = dbx\AppInfo::loadFromJsonFile('../php/private_data/dropbox_app.json');
	$clientIdentifier = DROPBOX_APP_NAME;
	$redirectUri      = 'http://'.WEB_PATH.'external-web-files/dropbox-response.php';
	$csrfTokenStore   = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
	return new dbx\WebAuth($appInfo, $clientIdentifier, $redirectUri, $csrfTokenStore);
}
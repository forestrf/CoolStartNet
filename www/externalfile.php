<?php
// While using windows as server, pipelined connections of multiple php files crashes the server
// http://stackoverflow.com/questions/25255415/apache-php-crashes-when-calling-2-or-more-php-files-at-the-same-time
if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
	header('Connection: Close');
}

session_start();
if(!isset($_SESSION['user']) || !isset($_SESSION['user']['dropbox_accessToken'])){
	exit;
}

require_once 'php/config.php';

# Include the Dropbox SDK libraries
require_once dirname(__FILE__).'/php/lib/Dropbox/autoload.php';
use \Dropbox as dbx;

// Ask for the variables to identify the filename + path.
// name
if(!isset($_GET['file']) || strlen($_GET['file']) < 1){
	exit;
}

$dbxClient = new dbx\Client($_SESSION['user']['dropbox_accessToken'], DROPBOX_APP_NAME);

$file = &$_GET['file'];

ob_start();

$f = fopen('php://output', 'w');
$fileMetadata = $dbxClient->getFile($file, $f);
fclose($f);

$data = ob_get_contents();

ob_end_clean();
   
header('Content-type: '.$fileMetadata['mime_type']);

echo $data;
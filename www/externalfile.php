<?php
// While using windows as server, pipelined connections of multiple php files crashes the server
// http://stackoverflow.com/questions/25255415/apache-php-crashes-when-calling-2-or-more-php-files-at-the-same-time
if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
	header('Connection: Close');
}

// Ask for the variables to identify the filename + path.
// name
if(!isset($_GET['file']) || strlen($_GET['file']) < 1){
	exit;
}
$file = &$_GET['file'];

$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;
$etag = base64_encode($file);

if($if_none_match === $etag){
	header('HTTP/1.1 304 Not Modified');
	exit;
}

session_start();
if(!isset($_SESSION['user']) || !isset($_SESSION['user']['dropbox_accessToken'])){
	exit;
}

require_once 'php/config.php';

# Include the Dropbox SDK libraries
require_once dirname(__FILE__).'/php/lib/Dropbox/autoload.php';
use \Dropbox as dbx;


$dbxClient = new dbx\Client($_SESSION['user']['dropbox_accessToken'], DROPBOX_APP_NAME);

// Save the file in the buffer cache and then store the buffer in $data
ob_start();
$f = fopen('php://output', 'w');
$fileMetadata = $dbxClient->getFile($file, $f);
fclose($f);
$data = ob_get_contents();
ob_end_clean();

// Headers and file in order

header('Content-type: '.$fileMetadata['mime_type']);

header('Pragma: public');
header('Etag: '.base64_encode($file));
header('Cache-Control: max-age=2592000, public'); // 30 days

echo $data;
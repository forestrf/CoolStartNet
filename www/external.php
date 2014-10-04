<?php
// While using windows as server, pipelined connections of multiple php files crashes the server
// http://stackoverflow.com/questions/25255415/apache-php-crashes-when-calling-2-or-more-php-files-at-the-same-time
header('Connection: Close');

require_once __DIR__.'/php/defaults.php';
require_once __DIR__.'/php/functions/generic.php';

$db = open_db_session();
user_check_access();

// Ask for the variables to identify the filename + path.
if (isset($_REQUEST['m'])) {
	switch ($_REQUEST['m']) {
		case '0':
			if (isset($_POST['path']) &&
					isset($_POST['path'][0]) &&
					$_POST['path'][0] === '/' &&
					isset($_SESSION['user']['dropbox_accessToken'])){
				echoPath(start(), $_POST['path']);
			}
		break;
		case '1':
			if (isset($_SESSION['user'])) {
				echo json_encode(
					array(
						'available' => isset($_SESSION['user']['dropbox_accessToken'])
					)
				);
			}
		break;
		case '2':
			if (isset($_GET['file']) &&
					isset($_GET['file'][0]) &&
					$_GET['file'][0] === '/' &&
					isset($_SESSION['user']['dropbox_accessToken'])) {
				
				if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === base64_encode($_GET['file'])) {
					header('HTTP/1.1 304 Not Modified');
					exit;
				}
				
				echoFile(start(), $_GET['file']);
			}
		break;
	}
}



function start(){
	# Include the Dropbox SDK libraries
	require_once __DIR__.'/php/lib/Dropbox/autoload.php';
	
	return new \Dropbox\Client($_SESSION['user']['dropbox_accessToken'], DROPBOX_APP_NAME);
}

function echoPath(&$dbxClient, &$path){
	$folderMetadata = $dbxClient->getMetadataWithChildren($path);
	$response = array(
		'folders' => array(),
		'files'   => array()
	);
	if ($folderMetadata and $folderMetadata['is_dir'] === true) {
		foreach ($folderMetadata['contents'] as $elem) {
			switch ($elem['is_dir']) {
				case true:
					$response['folders'][] = $elem['path'];
				break;
				case false:
				default:
					$response['files'][] = $elem['path'];
				break;
			}
		}
	}
	echo json_encode($response);
}

function echoFile(&$dbxClient, &$file){
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
}
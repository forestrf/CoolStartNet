<?php
// A file can only be downloaded if the user is using the widget or is the owner of the widget.
// If the name of the file exists for the version asked or exists for the current version the php echoes the file and sends the mimetype header of the file.

// While using windows as server, pipelined connections of multiple php files crashes the server
// Sessions are fucked up too
// http://stackoverflow.com/questions/25255415/apache-php-crashes-when-calling-2-or-more-php-files-at-the-same-time

header('Connection: Close');

require_once __DIR__.'/php/defaults.php';
require_once __DIR__.'/php/functions/generic.php';

$db = open_db_session();
user_check_access(true);



// Ask for the variables to identify the file.
// widgetID
if (!isset($_GET['widgetID']) || !isInteger($_GET['widgetID']) || $_GET['widgetID'] < 0) {
	exit;
}
$widgetID = &$_GET['widgetID'];

// name
if (!isset($_GET['name']) || strlen($_GET['name']) > FILENAME_MAX_LENGTH || strlen($_GET['name']) < 1) {
	exit;
}
$name = &$_GET['name'];

// mode
if (!isset($_GET['mode'])) {
	exit;
}
$mode = &$_GET['mode'];


// widgetVersion
switch($mode) {
	case 'static':
		$widgetVersion = -1;
		break;
	case 'api':
		$widgetVersion = $db->get_using_widget_version_user($widgetID);
		break;
	default:
		if (!isInteger($mode) || $mode < 0) {
			exit;
		}
		$widgetVersion = $mode;
		break;
}

$file = $db->get_widget_version_file($widgetID, $widgetVersion, $name);

if ($file) {
	$etag = base64_encode(md5($file['hash']));
	
	header('Pragma: public');
	header('Etag: ' . $etag);
	header('Cache-Control: max-age=2592000, public'); // 30 days
	
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
		header('HTTP/1.1 304 Not Modified');
		exit;
	} else {
		// var_dump($file[0]);
		header('Content-type: '.$file['mimetype']);
		
		readfile($db->get_widget_file_path_from_hash($file['hash']));
	}
}

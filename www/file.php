<?php
// A file can only be downloaded if the user is using the widget or is the owner of the widget.
// If the name of the file exists for the version asked or exists for the current version the php echoes the file and sends the mimetype header of the file.

// While using windows as server, pipelined connections of multiple php files crashes the server
// Sessions are fucked up too
// http://stackoverflow.com/questions/25255415/apache-php-crashes-when-calling-2-or-more-php-files-at-the-same-time

// Without this there is a bug sometimes with simultaneous request to php in the same http request
// header('Connection: Close');

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

// if the request comes with a correct referer, force the widget file
$correct_referer = isset($_SERVER['HTTP_REFERER']) && preg_match('@^https?://' . WEB_PATH . '@', $_SERVER['HTTP_REFERER']);

$file = $db->get_widget_file($widgetID, $name, $correct_referer);

if ($file) {
	$etag = base64_encode(md5($file['hash']));
	
	header('Etag: ' . $etag);
	header('Cache-Control: max-age=120, public'); // 2 min. This is a problem when developing. Force check.
	
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
		header('HTTP/1.1 304 Not Modified');
		exit;
	} else {
		// var_dump($file[0]);
		header('Content-type: ' . file_mimetype($name));
		
		readfile($db->get_widget_file_path_from_hash($file['hash']));
	}
}


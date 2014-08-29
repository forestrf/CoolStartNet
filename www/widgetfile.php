<?php
// A file can only be downloaded if the user is using the widget or is the owner of the widget.
// If the name of the file exists for the version asked or exists for the current version the php echoes the file and sends the mimetype header of the file.

// While using windows as server, pipelined connections of multiple php files crashes the server
// Sessions are fucked up too
// http://stackoverflow.com/questions/25255415/apache-php-crashes-when-calling-2-or-more-php-files-at-the-same-time

header('Connection: Close');

require_once 'php/functions/generic.php';
$db = open_db_session();
if(!isset($_SESSION['user'])){
	exit;
}



// Ask for the variables to identify the file.
// widgetID
if(!isset($_GET['widgetID']) || !isInteger($_GET['widgetID']) || $_GET['widgetID'] < 0){
	exit;
}
$widgetID = &$_GET['widgetID'];

// name
if(!isset($_GET['name']) || strlen($_GET['name']) > FILENAME_MAX_LENGTH || strlen($_GET['name']) < 1){
	exit;
}
$name = &$_GET['name'];



// If the call comes from the api the widget version comes from a query to the database.
if(isset($_GET['api'])){
	// widgetVersion
	$widgetVersion = $db->get_using_widget_version_user($widgetID);
}
else{
	// widgetVersion
	if(!isset($_GET['widgetVersion']) || !isInteger($_GET['widgetVersion']) || $_GET['widgetVersion'] < 0){
		exit;
	}
	$widgetVersion = &$_GET['widgetVersion'];
}


$file = $db->get_widget_version_file($widgetID, $widgetVersion, $name);

if($file){
	// var_dump($file[0]);
	header('Content-type: '.$file[0]['mimetype']);
	echo $file[0]['data'];
}
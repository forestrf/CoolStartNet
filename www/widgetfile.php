<?php
// Los archivos solo pueden descargarse si el usuario que los pide tiene el widget en su cuenta o es el creador del widget. Una vez comprobado, se coje mira si el nombre del archivo
// Existe en la versi�n del widget y de ser as�, se env�a el archivo respetando el tipo de arhivo que es (Mirar esto �ltimo c�mo hacerlo).

session_start();
if(!isset($_SESSION['user'])){
	exit;
}


require_once 'php/config.php';
require_once 'php/class/DB.php';

// Pedir las variables para identificar widget, version y nombre de archivo.
// widgetID
if(!isset($_GET['widgetID']) || !isInteger($_GET['widgetID']) || $_GET['widgetID'] < 0){
	exit;
}

// widgetVersion
if(!isset($_GET['widgetVersion']) || !isInteger($_GET['widgetVersion']) || $_GET['widgetVersion'] < 0){
	exit;
}

// nombre
if(!isset($_GET['name']) || strlen($_GET['name']) > FILENAME_MAX_LENGTH || strlen($_GET['name']) < 1){
	exit;
}

$widgetID = &$_GET['widgetID'];
$widgetVersion = &$_GET['widgetVersion'];
$name = &$_GET['name'];

$db = new DB();

$file = $db->widgetVersionGetArchivo($widgetID, $widgetVersion, $name);

if($file){
	$file = &$file[0];
	// var_dump($file);
	header('Content-type: '.$file['mimetype']);
	echo $file['data'];
}
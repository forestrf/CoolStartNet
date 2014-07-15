<?php
// Los archivos solo pueden descargarse si el usuario que los pide tiene el widget en su cuenta o es el creador del widget. Una vez comprobado, se coje mira si el nombre del archivo
// Existe en la versión del widget y de ser así, se envía el archivo respetando el tipo de arhivo que es (Mirar esto último cómo hacerlo).

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
$widgetID = &$_GET['widgetID'];

// nombre
if(!isset($_GET['name']) || strlen($_GET['name']) > FILENAME_MAX_LENGTH || strlen($_GET['name']) < 1){
	exit;
}
$name = &$_GET['name'];



$db = new DB();

// Si se llama desde la api no se suministra la versión del widget. En su lugar se usa la que está indicada en la db.
if(isset($_GET['api'])){
	// widgetVersion
	$widgetVersion = $db->getWidgetUserVersion($widgetID);
}
else{
	// widgetVersion
	if(!isset($_GET['widgetVersion']) || !isInteger($_GET['widgetVersion']) || $_GET['widgetVersion'] < 0){
		exit;
	}
	$widgetVersion = &$_GET['widgetVersion'];
}


$file = $db->widgetVersionGetArchivo($widgetID, $widgetVersion, $name);

if($file){
	$file = &$file[0];
	// var_dump($file);
	header('Content-type: '.$file['mimetype']);
	echo $file['data'];
}
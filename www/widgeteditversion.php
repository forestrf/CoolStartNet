<?php

header('Content-Type: text/html; charset=UTF-8');

session_start();
if(!isset($_SESSION['usuario'])){
	exit;
}


require_once 'php/config.php';
require_once 'php/clases/DB.php';
require_once 'php/funciones/genericas.php';

if(!isset($_GET['widgetID']) || !isInteger($_GET['widgetID']) || $_GET['widgetID'] < 0){
	exit;
}
if(!isset($_GET['widgetVersion']) || !isInteger($_GET['widgetVersion']) || $_GET['widgetVersion'] < 0){
	exit;
}
$widgetID = &$_GET['widgetID'];

$db = new DB();

$widget = $db->getWidgetPorID($widgetID);
$versiones = $db->getWidgetVersiones($widgetID);

?>
<!doctype html>
<html>
<head>
	<title>Editar widget</title>
	<!--<link rel="stylesheet" href="css/reset.min.css"/>-->
	<style>
		form {
			display: inline;
		}
	</style>
</head>
<body>

Edita una versión de un widget agregando y quitando archivos.<br/>
Tirar de post<br/><br/>

Comprobar que el widget tiene la versión y dicha versión no es pública<br/><br/>

Archivos:
Es obligatorio el archivo "main.js" ya que será el único que se incruste. Mediante la api se pueden llamar otros archivos. (por hacer)

</body>
</html>
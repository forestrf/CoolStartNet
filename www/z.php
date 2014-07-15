<?php

session_start();
if(!isset($_SESSION['user'])){
	exit;
}


require_once 'php/config.php';
require_once 'php/class/DB.php';
require_once 'php/functions/generic.php';

$db = new DB();

?>

<!doctype html>
<html>
<head>
	<title>Homepage</title>
	<script src="js/api.js"></script>
	<link rel="stylesheet" href="css/reset.min.css"/>
</head>
<body>


Mirar qué widgets tiene el usuario

Recorrerlos todos e incluir el js que les corresponde.

Un widget debe de contener lo siguiente: 
- Javascript que lo compone
- Posición y tamaño donde está situado

El javascript dibujará todo el widget, incluido el div que lo contendrá en el body
El javascript debe de estar contenido en su totalidad en una función anónima
El javascript tendrá acceso a la API para leer y escribir variables de cualquier widget. Además la api le suministrará la url a los archivos que le pida.
El javascript tendrá acceso a la posición y tamaño indicado y podrá editarlo ya que serán variables accesibles desde la API


<?php

// Widgets del usuario
$widgets_usuario = $db->getWidgetsDelUsuario();
foreach($widgets_usuario as &$widget){
	// Pick the correct widget version
	$version = $db->getWidgetUserVersion($widget);
	
	// Create the html that will call the script
	//echo "<script src=\"widgetfile.php?widgetID={$widget['ID']}&widgetVersion={$version}&name=main.js\"></script>";
	$data = $db->widgetVersionGetArchivo($widget['ID'], $version, 'main.js');
	$data = &$data[0];
	$data = &$data['data'];
	echo "<script>(function(widgetID, versionWidget){{$data}})('{$widget['ID']}', '{$version}');</script>";
}
?>

</body>
</html>
<?php

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
$widgetID = &$_GET['widgetID'];

$db = new DB();

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

Edita un widget agregando archivos y versiones y variables<br/>
No se pueden borrar ni modificar versiones públicas<br/>
Tirar de post<br/><br/>


<form method="POST" action="ipa.php">
	<input type="hidden" name="switch" value="3">
	<input type="hidden" name="accion" value="1">
	<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
	<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
	<input type="hidden" name="volver" value="1">
	<input type="submit" value="Crear nueva versión">
</form><br/>
<?php
$versiones = $db->getWidgetVersiones($widgetID);
if(count($versiones) > 0){
	foreach($versiones as $version){
		echo $version['version'].'<br/>';
	}
	/*$version = 1;
	var_dump($db->getWidgetContenidoVersion($widgetID, $version));
	*/
}
else{
	echo 'Este widget no cuenta con ninguna versión.';
}

?>




<?php

// Widgets del usuario

?>

</body>
</html>
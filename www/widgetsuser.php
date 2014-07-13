<?php

header('Content-Type: text/html; charset=UTF-8');

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
	<title>Widgets used by the user</title>
	<!--<link rel="stylesheet" href="css/reset.min.css"/>-->
	<style>
		form {
			display: inline;
		}
	</style>
</head>
<body>

Add and remove widgets for the user.<br/>

In use:<br/>
<?php
$widgets_usuario = $db->getWidgetsDelUsuario();

foreach($widgets_usuario as &$widget){
	echo $widget['nombre'].' (<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="1">
			<input type="hidden" name="accion" value="1">
			<input type="hidden" name="widgetID" value="'.$widget['ID'].'">
			<input type="hidden" name="token" value="'.hash_ipa($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_IPA).'">
			<input type="hidden" name="volver" value="1">
			<input type="submit" value="Remove">
		</form>) Select a version from the widget | Use always the last version<br/>';
}
?>

<br/><br/>
Available widgets:<br/>
<?php
$widgets_disponibles = $db->getWidgetsDisponiblesUsuario();

if($widgets_disponibles){
	foreach($widgets_disponibles as &$widget){
		if(in_array($widget, $widgets_usuario)){
			echo $widget['nombre'].' (en uso).<br/>';
		}
		else{
			echo $widget['nombre'].' (<form method="POST" action="ipa.php">
					<input type="hidden" name="switch" value="1">
					<input type="hidden" name="accion" value="2">
					<input type="hidden" name="widgetID" value="'.$widget['ID'].'">
					<input type="hidden" name="token" value="'.hash_ipa($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_IPA).'">
					<input type="hidden" name="volver" value="1">
					<input type="submit" value="Use">
				</form>)<br/>';
		}
	}
}
?>

</body>
</html>
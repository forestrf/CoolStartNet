<?php

session_start();
if(!isset($_SESSION['usuario'])){
	exit;
}


require_once 'php/config.php';
require_once 'php/clases/DB.php';
require_once 'php/funciones/genericas.php';

$db = new DB();

?>
<!doctype html>
<html>
<head>
	<title>Crear y borrar Widgets</title>
	<!--<link rel="stylesheet" href="css/reset.min.css"/>-->
	<style>
		form {
			display: inline;
		}
	</style>
</head>
<body>

Crear y borrar widgets del listado general<br/>
Tirar de post<br/><br/>


<form method="POST" action="ipa.php">
	Nombre Widget: <input type="text" name="nombre">
	<input type="hidden" name="switch" value="2">
	<input type="hidden" name="accion" value="1">
	<input type="hidden" name="widgetID" value="-1">
	<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['usuario']['RND'], -1, PASSWORD_TOKEN_IPA)?>">
	<input type="hidden" name="volver" value="1">
	<input type="submit" value="Crear">
</form>

<br/><br/>
Widgets que he creado:<br/>
<?php
$widgets = $db->getWidgetsControlUsuario();

foreach($widgets as &$widget){
	echo $widget['nombre'].' (
		<form method="GET" action="widgetedit.php">
			<input type="hidden" name="widgetID" value="'.$widget['ID'].'">
			<input type="submit" value="Editar">
		</form>
		<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="2">
			<input type="hidden" name="accion" value="2">
			<input type="hidden" name="widgetID" value="'.$widget['ID'].'">
			<input type="hidden" name="token" value="'.hash_ipa($_SESSION['usuario']['RND'], $widget['ID'], PASSWORD_TOKEN_IPA).'">
			<input type="hidden" name="volver" value="1">
			<input type="submit" value="Borrar">
		</form>)<br/>';
}

?>




<?php

// Widgets del usuario

?>

</body>
</html>
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
$version = &$_GET['widgetVersion'];

$db = new DB();

$widget = $db->getWidgetPorID($widgetID);
$version = $db->getWidgetVersion($widgetID, $version);

// Si la versión es pública no se puede editar.
if($version['publico'] === '1'){
	exit;
}

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

Archivos:<br/>
Es obligatorio el archivo "main.js" ya que será el único que se incruste. Mediante la api se pueden llamar otros archivos. (por hacer)<br/><br/>

<input type="submit" value="Copiar archivos de la versión anterior">

<?php
$archivos = $db->getWidgetContenidoVersion($widgetID, $version['version']);
foreach($archivos as $archivo){
	?>
	<form method="POST" action="ipa.php" enctype="multipart/form-data">
		<input type="hidden" name="switch" value="4">
		<input type="hidden" name="accion" value="1">
		<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
		<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
		<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
		<input type="hidden" name="hash" value="<?php echo $archivo['hash']?>">
		<input type="text" name="nombre" value="<?php echo $archivo['nombre']?>"><br/>
		<input type="submit" value="Cambiar nombre">
		<input type="hidden" name="volver" value="1">
	</form>
	
	<?php
	echo $archivo['nombre'].' (Cambiar nombre | Actualizar archivo | Borrar)<br/>';
}
?>
<form method="POST" action="ipa.php" enctype="multipart/form-data">
	<input type="hidden" name="switch" value="4">
	<input type="hidden" name="accion" value="1">
	<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
	<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
	<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
	<input type="text" name="nombre" value="archivo.extension"><br/>
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo TAM_BYTES_ARCHIVOS_MAX?>" /> Tam. Max: <?php echo TAM_BYTES_ARCHIVOS_MAX/1024?>Kb
	<input type="file" name="archivo"><br/>
	<input type="submit" value="enviar">
	<input type="hidden" name="volver" value="1">
</form>





<br/><br/>

</body>
</html>
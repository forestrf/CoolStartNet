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

Edita un widget administrando sus versiones<br/>
No se pueden borrar ni modificar versiones públicas pero se puede ocultar. Aquellos que lo tengan lo seguirán teniendo pero nadie más lo podrá agregar.<br/>
Tirar de post<br/><br/>

¿Posibilidad de renombrar?<br/><br/>


<form method="POST" action="ipa.php">
	<input type="hidden" name="switch" value="3">
	<input type="hidden" name="accion" value="1">
	<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
	<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
	<input type="hidden" name="volver" value="1">
	<input type="submit" value="Crear nueva versión">
</form><br/>
<?php
$widget = $db->getWidgetPorID($widgetID);
$versiones = $db->getWidgetVersiones($widgetID);
if(count($versiones) > 0){
	foreach($versiones as $version){
		echo '['.$version['version'].']',$version['publico']?($version['visible']?'+ ':'- '):' ';
		
		?>
		<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="5">
			<input type="hidden" name="accion" value="6">
			<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
			<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
			<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
			<input type="text" name="comentario" value="<?php echo $version['comentario']?>">
			<input type="submit" value="comentar" maxlength="250">
			<input type="hidden" name="volver" value="1">
		</form>
		<?php
		if($version['publico'] === '1'){
			if($widget['publicado'] !== $version['version']){
			?>
			<form method="POST" action="ipa.php">
				<input type="hidden" name="switch" value="5">
				<input type="hidden" name="accion" value="1">
				<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
				<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
				<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
				<input type="hidden" name="volver" value="1">
				<input type="submit" value="Convertir en versión actual">
			</form>
			<?php } ?>
			<form method="POST" action="ipa.php">
				<input type="hidden" name="switch" value="5">
				<input type="hidden" name="accion" value="<?php echo $version['visible']?2:3?>">
				<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
				<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
				<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
				<input type="hidden" name="volver" value="1">
				<input type="submit" value="<?php echo $version['visible']?'Ocultar de publicaciones':'Hacer visible'?>">
			</form>
			<?php
		}
		else{
			?>
			<form method="GET" action="widgeteditversion.php">
				<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
				<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
				<input type="submit" value="Editar">
			</form>
			<form method="POST" action="ipa.php">
				<input type="hidden" name="switch" value="3">
				<input type="hidden" name="accion" value="2">
				<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
				<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
				<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
				<input type="hidden" name="volver" value="1">
				<input type="submit" value="Borrar">
			</form>
			<form method="POST" action="ipa.php">
				<input type="hidden" name="switch" value="5">
				<input type="hidden" name="accion" value="4">
				<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
				<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
				<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
				<input type="hidden" name="volver" value="1">
				<input type="submit" value="Publicar">
			</form>
			<?php
		}
		
		if($widget['publicado'] === $version['version']){
			echo 'Versión actual';
		}
		
		echo '<br/>';
	}
	/*
	$version = 1;
	var_dump($db->getWidgetContenidoVersion($widgetID, $version));
	*/
}
else{
	echo 'Este widget no cuenta con ninguna versión.';
}

?>

PONER ESTO EN EL PHP ABIERTO POR BOTÓN EDITAR LA VERSIÓN.
Script:
Hueco para adjuntar el script.
Elementos:
Hueco para adjuntar elementos.

<?php

// Widgets del usuario

?>

</body>
</html>
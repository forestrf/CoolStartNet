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
	<title>Create and delete widgets</title>
	<!--<link rel="stylesheet" href="css/reset.min.css"/>-->
	<style>
		form {
			display: inline;
		}
	</style>
</head>
<body>

Create and delete widgets<br/>


<form method="POST" action="ipa.php">
	Widget name: <input type="text" name="nombre">
	<input type="hidden" name="switch" value="2">
	<input type="hidden" name="accion" value="1">
	<input type="hidden" name="widgetID" value="-1">
	<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['user']['RND'], -1, PASSWORD_TOKEN_IPA)?>">
	<input type="hidden" name="volver" value="1">
	<input type="submit" value="Crear">
</form>

<br/><br/>
Widgets that I created:<br/>
<?php
$widgets = $db->getWidgetsControlUsuario();

foreach($widgets as &$widget){
	echo $widget['name'].' (
		<form method="GET" action="widgetedit.php">
			<input type="hidden" name="widgetID" value="'.$widget['ID'].'">
			<input type="submit" value="Edit">
		</form>';
	if($widget['published'] === '-1'){
		echo '<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="2">
			<input type="hidden" name="accion" value="2">
			<input type="hidden" name="widgetID" value="'.$widget['ID'].'">
			<input type="hidden" name="token" value="'.hash_ipa($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_IPA).'">
			<input type="hidden" name="volver" value="1">
			<input type="submit" value="Delete">
		</form>';
	}
	else{
		?>
		<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="5">
			<input type="hidden" name="accion" value="5">
			<input type="hidden" name="widgetID" value="<?php echo $widget['ID']?>">
			<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_IPA)?>">
			<input type="hidden" name="volver" value="1">
			<input type="submit" value="Hide from the public">
		</form>
		<?php
	}
	echo ')<br/>';
}

?>

</body>
</html>
<?php

header('Content-Type: text/html; charset=UTF-8');

session_start();
if(!isset($_SESSION['user'])){
	exit;
}


require_once 'php/config.php';
require_once 'php/class/DB.php';
require_once 'php/functions/generic.php';


if(!isset($_GET['widgetID']) || !isInteger($_GET['widgetID']) || $_GET['widgetID'] < 0){
	exit;
}
$widgetID = &$_GET['widgetID'];

$db = new DB();

$widget = $db->get_widget_user($widgetID);
if(!$widget){
	exit;
}

?>
<!doctype html>
<html>
<head>
	<title>Select the version of the widget</title>
	<style>
		form {
			display: inline;
		}
	</style>
</head>
<body>

List of widget versions.<br/>

<?php
$widget_versions = $db->get_all_widget_versions($widget['ID']);

$current = false;

foreach($widget_versions as &$widget_version){
	echo 'Version '.$widget_version['version'],
		' (<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="1">
			<input type="hidden" name="accion" value="3">
			<input type="hidden" name="widgetID" value="'.$widget['ID'].'">
			<input type="hidden" name="token" value="'.hash_ipa($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_IPA).'">
			<input type="hidden" name="widgetVersion" value="'.$widget_version['version'].'">
			<input type="hidden" name="volver" value="1">
			<input type="submit" value="Use this version">
		</form>)';
	if(!$current && (
		($widget['autoupdate'] !== '1' && $widget_version['version'] === $widget['version']) || 
		($widget['autoupdate'] === '1' && $widget_version['public'] === '1' && $widget_version['visible'] === '1')
	)){
		echo ' (Current)';
		$current = true;
	}
	echo '<br/>';
}
?>

</body>
</html>
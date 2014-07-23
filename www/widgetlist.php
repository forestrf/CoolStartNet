<?php

header('Content-Type: text/html; charset=UTF-8');

session_start();
if(!isset($_SESSION['user'])){
	exit;
}


require_once 'php/config.php';
require_once 'php/class/DB.php';
require_once 'php/functions/generic.php';

inser_nocache_headers();

$db = new DB();

?>
<!doctype html>
<html>
<head>
	<title>Create and delete widgets</title>
	<style>
		form {
			display: inline;
		}
	</style>
</head>
<body>

Create and delete widgets<br/>


<form method="POST" action="ipa.php">
	Widget name: <input type="text" name="name">
	<input type="hidden" name="switch" value="2">
	<input type="hidden" name="action" value="1">
	<input type="hidden" name="widgetID" value="-1">
	<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['user']['RND'], -1, PASSWORD_TOKEN_IPA)?>">
	<input type="hidden" name="goback" value="1">
	<input type="submit" value="Create">
</form>

<br/><br/>
Widgets that I created:<br/>
<?php
$widgets = $db->get_widgets_user_owns();

foreach($widgets as &$widget){
	echo $widget['name'].' (
		<form method="GET" action="widgetedit.php">
			<input type="hidden" name="widgetID" value="'.$widget['ID'].'">
			<input type="submit" value="Edit">
		</form>';
	if($widget['published'] === '-1'){
		echo '<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="2">
			<input type="hidden" name="action" value="2">
			<input type="hidden" name="widgetID" value="'.$widget['ID'].'">
			<input type="hidden" name="token" value="'.hash_ipa($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_IPA).'">
			<input type="hidden" name="goback" value="1">
			<input type="submit" value="Delete">
		</form>';
	}
	else{
		?>
		<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="5">
			<input type="hidden" name="action" value="5">
			<input type="hidden" name="widgetID" value="<?php echo $widget['ID']?>">
			<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_IPA)?>">
			<input type="hidden" name="goback" value="1">
			<input type="submit" value="Hide from the public">
		</form>
		<?php
	}
	echo ')<br/>';
}

?>

</body>
</html>
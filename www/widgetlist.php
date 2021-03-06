<?php

header('Content-Type: text/html; charset=UTF-8');

require_once 'php/functions/generic.php';
$db = open_db_session();
if(!G::$SESSION->exists()){
	exit;
}


insert_nocache_headers();

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
	<input type="hidden" name="token" value="<?php echo hash_ipa(G::$SESSION->get_user_random(), -1, PASSWORD_TOKEN_IPA)?>">
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
			<input type="hidden" name="widgetID" value="'.$widget['IDwidget'].'">
			<input type="submit" value="Edit">
		</form>';
	if($widget['published'] === '-1'){
		echo '<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="2">
			<input type="hidden" name="action" value="2">
			<input type="hidden" name="widgetID" value="'.$widget['IDwidget'].'">
			<input type="hidden" name="token" value="'.hash_ipa(G::$SESSION->get_user_random(), $widget['IDwidget'], PASSWORD_TOKEN_IPA).'">
			<input type="hidden" name="goback" value="1">
			<input type="submit" value="Delete">
		</form>';
	}
	else{
		?>
		<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="5">
			<input type="hidden" name="action" value="5">
			<input type="hidden" name="widgetID" value="<?php echo $widget['IDwidget']?>">
			<input type="hidden" name="token" value="<?php echo hash_ipa(G::$SESSION->get_user_random(), $widget['IDwidget'], PASSWORD_TOKEN_IPA)?>">
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
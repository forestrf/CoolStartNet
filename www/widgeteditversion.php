<?php

header('Content-Type: text/html; charset=UTF-8');

session_start();
if(!isset($_SESSION['user'])){
	exit;
}


require_once 'php/config.php';
require_once 'php/class/DB.php';
require_once 'php/functions/generic.php';

insert_nocache_headers();

if(!isset($_GET['widgetID']) || !isInteger($_GET['widgetID']) || $_GET['widgetID'] < 0){
	exit;
}
if(!isset($_GET['widgetVersion']) || !isInteger($_GET['widgetVersion']) || $_GET['widgetVersion'] < 0){
	exit;
}
$widgetID = &$_GET['widgetID'];
$version = &$_GET['widgetVersion'];

$db = new DB();

$widget = $db->get_widget_by_ID($widgetID);
if(!$widget){
	exit;
}
$version = $db->get_widget_version($widgetID, $version);
if(!$version){
	exit;
}

// If the version is public cannot be edited.
if($version['public'] === '1'){
	exit;
}

?>
<!doctype html>
<html>
<head>
	<title>Edit widget</title>
	<style>
		form {
			display: inline;
		}
	</style>
</head>
<body>

Edit a version of the widget <b>"<?php echo $widget['name']?>"</b> adding or removing files to it.<br/>

Files:<br/>

<form>
	<input type="submit" value="Copy files from the previous version"> This will delete the files of this version (Por hacer)
</form>
<br/><br/>

Files:<br/>
<?php
$files = $db->get_widget_version_contents($widgetID, $version['version']);
foreach($files as $file){
	?>
	<form method="POST" action="ipa.php">
		<input type="hidden" name="switch" value="4">
		<input type="hidden" name="action" value="3">
		<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
		<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
		<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['user']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
		<input type="hidden" name="hash" value="<?php echo $file['hash']?>">
		<input type="text" name="name" value="<?php echo $file['name']?>">
		<input type="submit" value="Change name">
		<input type="hidden" name="goback" value="1">
	</form>
	
	(
		<form method="GET" action="widgetfile.php" target="_blank">
			<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
			<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
			<input type="hidden" name="name" value="<?php echo $file['name']?>">
			<input type="submit" value="SEE">
		</form> 
		<form method="POST" action="ipa.php" enctype="multipart/form-data">
			<input type="hidden" name="switch" value="4">
			<input type="hidden" name="action" value="4">
			<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
			<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
			<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['user']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
			<input type="hidden" name="hash" value="<?php echo $file['hash']?>">
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE_BYTES?>" /> Max size: <?php echo MAX_FILE_SIZE_BYTES/1024?>Kb
			<input type="file" name="file">
			<input type="submit" value="Send new version">
			<input type="hidden" name="goback" value="1">
		</form>
		<form method="POST" action="ipa.php">
			<input type="hidden" name="switch" value="4">
			<input type="hidden" name="action" value="2">
			<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
			<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
			<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['user']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
			<input type="hidden" name="hash" value="<?php echo $file['hash']?>">
			<input type="submit" value="Delete">
			<input type="hidden" name="goback" value="1">
		</form>
	)<br/>
	<?php
}
?>
<br/>
<form method="POST" action="ipa.php" enctype="multipart/form-data">
	<input type="hidden" name="switch" value="4">
	<input type="hidden" name="action" value="1">
	<input type="hidden" name="widgetID" value="<?php echo $widgetID?>">
	<input type="hidden" name="widgetVersion" value="<?php echo $version['version']?>">
	<input type="hidden" name="token" value="<?php echo hash_ipa($_SESSION['user']['RND'], $widgetID, PASSWORD_TOKEN_IPA)?>">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE_BYTES?>" /> Max size: <?php echo MAX_FILE_SIZE_BYTES/1024?>Kb
	<input type="file" name="file"><br/>
	<input type="submit" value="Send">
	<input type="hidden" name="goback" value="1">
</form>





<br/><br/>

</body>
</html>
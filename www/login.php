<?php
session_start();
if(isset($_POST['submit'])){
	require_once 'php/class/DB.php';
	
	$db = new DB();
	$valid = $db -> NickPasswordValidacion($_POST['nick'], $_POST['password']);
	
	if($valid !== false){
		$_SESSION['user'] = $valid;
	}
	else{
		echo 'Invalid login<br>';
	}
}

var_dump($_SESSION);

if(isset($_SESSION['user'])){
	exit;
}
?>

<form method="POST" action="">
	<input type="text" name="nick" placeholder="nick"><br>
	<input type="password" name="password" placeholder="password"><br>
	<input name="submit" type="submit" value="Login">
</form>

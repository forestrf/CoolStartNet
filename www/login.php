<?php
session_start();
if(isset($_POST['submit'])){
	require_once 'php/class/DB.php';
	
	$db = new DB();
	$valido = $db -> NickPasswordValidacion($_POST['nick'], $_POST['password']);
	
	if($valido !== false){
		$_SESSION['usuario'] = $valido;
	}
	else{
		echo 'login inválido<br>';
	}
}

var_dump($_SESSION);

if(isset($_SESSION['usuario'])){
	exit;
}
?>

<form method="POST" action="">
	<input type="text" name="nick" placeholder="nick"><br>
	<input type="password" name="password" placeholder="password"><br>
	<input name="submit" type="submit" value="Entrar">
</form>


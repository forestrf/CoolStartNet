<?php
session_start();
if(isset($_POST['submit'])){
	require_once 'php/clases/DB.php';
	
	$db = new DB();
	$valido = $db -> NickPasswordValidacion($_POST['nick'], $_POST['password']);
	
	if($valido !== false){
		
		$_SESSION['usuario'] = $valido;
		
		var_dump($valido);
		exit;
	}
	else{
		echo 'login inválido<br>';
	}
}

if(isset($_SESSION['usuario'])){
	echo 'estás logueado como '.$_SESSION['usuario']['Nick'];
	exit;
}

var_dump($_SESSION);
?>

<form method="POST" action="">
<input type="text" name="nick" placeholder="nick"><br>
<input type="password" name="password" placeholder="password"><br>
<input name="submit" type="submit" value="Entrar">
</form>


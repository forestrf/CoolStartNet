<form method="POST" action="">
<input type="text" name="nick" placeholder="nick"><br>
<input type="password" name="password" placeholder="password"><br>
<input name="submit" type="submit" value="Crear usuario">
</form>

<?php
if(isset($_POST['submit'])){
	require_once 'php/clases/DB.php';
	
	$db = new DB();
	$db -> insertaUsuario($_POST['nick'], $_POST['password']);
	
	echo '<br>Cuenta creada.';
}
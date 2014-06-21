<?php

session_start();
if(!isset($_SESSION['usuario'])){
	exit;
}


require_once 'php/clases/DB.php';

$db = new DB();

?>
<!doctype html>
<html>
<head>
	<title>Widgets con los que cuenta el usuario</title>
</head>
<body>

Agregar y quitar widgets para el usuario.<br>
Tirar de post<p>

En uso:<br>
<?php
foreach($db->getWidgetsDelUsuario() as $widget){
	echo $widget['nombre'].' (Quitar)<br>';
}
?>

<p>
Disponibles:<br>
<?php
foreach($db->getWidgetsDelUsuario() as $widget){
	echo $widget['nombre'].' (Usar)<br>';
}
?>




<?php

// Widgets del usuario

?>

</body>
</html>
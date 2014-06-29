<?php

session_start();
if(!isset($_SESSION['usuario'])){
	exit;
}


require_once 'php/config.php';
require_once 'php/clases/DB.php';
require_once 'php/funciones/genericas.php';

$db = new DB();

?>

<!doctype html>
<html>
<head>
	<title>Portada del usuario</title>
	<script src="js/api.js"></script>
	<link rel="stylesheet" href="css/reset.min.css"/>
</head>
<body>


Mirar qué widgets tiene el usuario

Recorrerlos todos e incluir el js que les corresponde.

Un widget debe de contener lo siguiente: 
- Javascript que lo compone
- Posición y tamaño donde está situado

El javascript dibujará todo el widget, incluido el div que lo contendrá en el body
El javascript debe de estar contenido en su totalidad en una función anónima
El javascript tendrá acceso a la API para leer y escribir variables de cualquier widget
El javascript tendrá accesp a la posición y tamaño indicado y podrá editarlo ya que serán variables accesibles desde la API

Si es necesario css, se escribirá mediante style en el js.
Si es necesario imágenes, se usará base64 en el js (lo siento...)


<?php

// Widgets del usuario
$widgets_usuario = $db->getWidgetsDelUsuario();
foreach($widgets_usuario as &$widget){
	print_r($widget);
}
?>

</body>
</html>
<?php

session_start();
if(!isset($_SESSION['usuario'])){
	exit;
}


require_once 'php/funciones/genericas.php';
require_once 'php/clases/DB.php';

$db = new DB();


// Esta api debe de llamarse solo por mi, no debe de funcionar llamándose desde algo que no sea la configuración de la web.
// Para controlar que no se haga nada raro se enviará un token y se bloqueará por referer.
// EL referer debe de ser la página de la que se puede configurar el valor en cuestión (Manejar mediante un array)
// El token se genera mediante un md5 de la variable que se va a cambiar, una contraseña y una variable que parta de la id del usuario (rnd).


// Por continuar. Comprobar referer y de coincidir, recoger datos, comprobar hash y de coincidir de nuevo, cambiar datos.
// hash_ipa($usuarioRND, $widgetID, PASSWORD_TOKEN_IPA);

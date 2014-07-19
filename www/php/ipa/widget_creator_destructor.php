<?php

if(!isset($_POST['nombre']) && !isset($_POST['widgetID'])){
	exit;
}

session_start();
if(!isset($_SESSION['user'])){
	exit;
}


require_once __DIR__.'/../config.php';
require_once __DIR__.'/../functions/generic.php';
require_once __DIR__.'/../class/DB.php';

$db = new DB();


// Esta api debe de llamarse solo por mi, no debe de funcionar llamándose desde algo que no sea la configuración de la web.
// Para controlar que no se haga nada raro se enviará un token y se bloqueará por referer.
// EL referer debe de ser la página de la que se puede configurar el valor en cuestión (Manejar mediante un array)
// El token se genera mediante un md5 de la variable que se va a cambiar, una contraseña y una variable que parta de la id del usuario (rnd).

/*
Acciones:
1 => Crear
2 => Borrar
*/

// Por continuar. Comprobar referer y de coincidir, recoger datos, comprobar hash y de coincidir de nuevo, cambiar datos.
// hash_ipa($_SESSION['user']['RND'], $widgetID, PASSWORD_TOKEN_IPA);

// Comprobar referer

$posibles_referers = array(
	'widgetlist.php'
);
print_r($_POST);
foreach($posibles_referers as $referer_temp){
	foreach(array('http', 'https') as $protocolo){
		if(strpos($_SERVER['HTTP_REFERER'], $protocolo.'://'.WEB_PATH.$referer_temp) === 0){
			// Referer válido
			
			switch($_POST['accion']){
				case '1':
					// Comprobar token
					if($_POST['token'] === hash_ipa($_SESSION['user']['RND'], -1, PASSWORD_TOKEN_IPA)){
						// Comprobar nombre
						if(isset($_POST['nombre']) && $_POST['nombre'] !== '' && preg_match('@[a-zA-Z0-9 ]{1,30}@', $_POST['nombre'])){
							$db->create_widget($_POST['nombre']);
						}
					}
				break;
				case '2':
					// Comprobar id
					if(isset($_POST['widgetID']) && isInteger($_POST['widgetID']) && $_POST['widgetID'] >= 0){
						// Comprobar token
						if($_POST['token'] === hash_ipa($_SESSION['user']['RND'], $_POST['widgetID'], PASSWORD_TOKEN_IPA)){
							$db->delete_widget($_POST['widgetID']);
						}
					}
				break;
			}
			break 2;
		}
	}
}
<?php

if(!isset($_POST['widgetID']) || !isset($_POST['accion']) || !isset($_POST['token'])){
	exit;
}

session_start();
if(!isset($_SESSION['usuario'])){
	exit;
}


require_once __DIR__.'/../config.php';
require_once __DIR__.'/../funciones/genericas.php';
require_once __DIR__.'/../clases/DB.php';

$db = new DB();


// Esta api debe de llamarse solo por mi, no debe de funcionar llam�ndose desde algo que no sea la configuraci�n de la web.
// Para controlar que no se haga nada raro se enviar� un token y se bloquear� por referer.
// EL referer debe de ser la p�gina de la que se puede configurar el valor en cuesti�n (Manejar mediante un array)
// El token se genera mediante un md5 de la variable que se va a cambiar, una contrase�a y una variable que parta de la id del usuario (rnd).

/*
Acciones:
1 => Quitar
2 => Poner
*/

// Por continuar. Comprobar referer y de coincidir, recoger datos, comprobar hash y de coincidir de nuevo, cambiar datos.
// hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA);

// Comprobar referer

$posibles_referers = array(
	'widgetsuser.php'
);

foreach($posibles_referers as $referer_temp){
	foreach(array('http', 'https') as $protocolo){
		if(strpos($_SERVER['HTTP_REFERER'], $protocolo.'://'.WEB_PATH.$referer_temp) === 0){
			// Referer v�lido
			
			// Comprobar token
			//print_r($_POST);
			$token_objetivo = hash_ipa($_SESSION['usuario']['RND'], $_POST['widgetID'], PASSWORD_TOKEN_IPA);
			if($_POST['token'] === $token_objetivo){
				
				switch($_POST['accion']){
					case '1':
						$db->quitarWidgetDelUsuario($_POST['widgetID']);
					break;
					case '2':
						$db->agregarWidgetAlUsuario($_POST['widgetID']);
					break;
				}
				if(isset($_POST['volver']) && $_POST['volver'] === '1'){
					header('HTTP/1.1 302 Moved Temporarily');
					header('Location: '.$_SERVER['HTTP_REFERER']); 
				}
				else{
					echo 'Completado';
				}
				exit;
			}
			echo 'Ha ocurrido un error.';
			exit;
		}
	}
}

echo 'Ha ocurrido un error.';

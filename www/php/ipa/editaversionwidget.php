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
require_once __DIR__.'/../class/DB.php';

$db = new DB();


// Esta api debe de llamarse solo por mi, no debe de funcionar llamándose desde algo que no sea la configuración de la web.
// Para controlar que no se haga nada raro se enviará un token y se bloqueará por referer.
// EL referer debe de ser la página de la que se puede configurar el valor en cuestión (Manejar mediante un array)
// El token se genera mediante un md5 de la variable que se va a cambiar, una contraseña y una variable que parta de la id del usuario (rnd).

/*
Acciones:
1 => Subir un archivo
2 => Borrar un archivo
3 => Renombrar archivo
*/

// Por continuar. Comprobar referer y de coincidir, recoger datos, comprobar hash y de coincidir de nuevo, cambiar datos.
// hash_ipa($_SESSION['usuario']['RND'], $widgetID, PASSWORD_TOKEN_IPA);

// Comprobar referer

$posibles_referers = array(
	'widgeteditversion.php'
);

foreach($posibles_referers as $referer_temp){
	foreach(array('http', 'https') as $protocolo){
		if(strpos($_SERVER['HTTP_REFERER'], $protocolo.'://'.WEB_PATH.$referer_temp) === 0){
			// Referer válido
			
			// Comprobar id
			if(isset($_POST['widgetID']) && isInteger($_POST['widgetID']) && $_POST['widgetID'] >= 0){
				// Comprobar token
				if($_POST['token'] === hash_ipa($_SESSION['usuario']['RND'], $_POST['widgetID'], PASSWORD_TOKEN_IPA)){
					if(isset($_POST['widgetVersion']) && isInteger($_POST['widgetVersion']) && $_POST['widgetVersion'] >= 0){
						switch($_POST['accion']){
							case '1':
								if(isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0){
									if($_FILES['archivo']['size'] <= MAX_FILE_SIZE_BYTES){
										$fp      = fopen($_FILES['archivo']['tmp_name'], 'rb');
										$content = fread($fp, filesize($_FILES['archivo']['tmp_name']));
										fclose($fp);
										
										// Innecesario borrarlo, php lo borra automaticamente.
										unlink($_FILES['archivo']['tmp_name']);
										
										$nombre = truncate_filename($_FILES['archivo']['name'], FILENAME_MAX_LENGTH);
										$tipo = $_FILES['archivo']['type'];
										
										$db->widgetVersionGuardarArchivo($_POST['widgetID'], $_POST['widgetVersion'], $nombre, $tipo, $content);
									}
								}
							break;
							case '2':
								if(isset($_POST['hash'])){
									$db->widgetVersionBorrarArchivo($_POST['widgetID'], $_POST['widgetVersion'], $_POST['hash']);
								}
							break;
							case '3':
								if(isset($_POST['hash'])){
									if(isset($_POST['nombre'])){
										$nombre = truncate_filename($_POST['nombre'], FILENAME_MAX_LENGTH);
										$db->widgetVersionRenombraArchivo($_POST['widgetID'], $_POST['widgetVersion'], $_POST['hash'], $nombre);
									}
								}
							break;
						}
					}
				}
			}
			break 2;
		}
	}
}
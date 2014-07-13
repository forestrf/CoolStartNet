<?php

header('Content-Type: text/html; charset=UTF-8');

if(!isset($_POST['switch'])){
	exit;
}

/*
1 => quitar o poner widgets en en la página del usuario
2 => crear o borrar widgets
3 => generar o quitar una versión para el widget
4 => editar una versión existente de un widget (subir un archivo con su nombre, editar nombre de un archivo, agregar o quitar un archivo o cambiar las variables)
5 => administrar versiones
*/

switch($_POST['switch']){
	case '1':
		require 'php/ipa/user_widget_list.php';
	break;
	case '2':
		require 'php/ipa/widget_creator_destructor.php';
	break;
	case '3':
		require 'php/ipa/widget_version_creator_destructor.php';
	break;
	case '4':
		require 'php/ipa/edit_widget_version.php';
	break;
	case '5':
		require 'php/ipa/manage_widget_versions.php';
	break;
}


if(isset($_POST['volver']) && $_POST['volver'] === '1'){
	header('HTTP/1.1 302 Moved Temporarily');
	header('Location: '.$_SERVER['HTTP_REFERER']); 
}
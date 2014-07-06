<?php

if(!isset($_POST['switch'])){
	exit;
}

/*
1 => quitar o poner widgets en en la página del usuario
2 => crear o borrar widgets
3 => generar una versión para el widget
4 => editar una versión existente de un widget (subir un archivo con su nombre, editar nombre de un archivo, agregar o quitar un archivo o cambiar las variables)
5 => administrar versiones
*/

switch($_POST['switch']){
	case '1':
		require 'php/ipa/quitarponerwidgetsuser.php';
	break;
	case '2':
		require 'php/ipa/creaborrawidgets.php';
	break;
	case '3':
		require 'php/ipa/creaversionwidget.php';
	break;
	case '4':
		require 'php/ipa/editaversionwidget.php';
	break;
	case '5':
		require 'php/ipa/adminversioneswidget.php';
	break;
}


if(isset($_POST['volver']) && $_POST['volver'] === '1'){
	header('HTTP/1.1 302 Moved Temporarily');
	header('Location: '.$_SERVER['HTTP_REFERER']); 
}
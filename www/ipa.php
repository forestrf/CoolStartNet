<?php

if(!isset($_POST['switch'])){
	exit;
}

/*
1 => quitar o poner widgets en en la página del usuario
2 => crear o borrar widgets
3 => generar una versión para el widget
4 => editar una versión existente de un widget (subir un archivo con su nombre, editar nombre de un archivo, agregar o quitar un archivo o cambiar las variables)
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
}
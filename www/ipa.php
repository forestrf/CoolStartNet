<?php

if(!isset($_POST['switch'])){
	exit;
}

/*
1 => quitar o poner widgets en en la p�gina del usuario
*/

switch($_POST['switch']){
	case '1':
		require 'php/ipa/quitarponerwidgetsuser.php';
	break;
}
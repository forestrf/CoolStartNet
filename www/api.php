<?php

// Ignore warnings. They can be harmful for the json response
error_reporting(E_ALL ^ E_WARNING);

session_start();
if(!isset($_SESSION['user'])){
	exit;
}


require_once 'php/functions/generic.php';
require_once 'php/class/DB.php';

$db = new DB();

/*
$_GET['data'] => json(
	widget => (
		variable => valor
	)
)
$_GET['action'] => get/set
*/

/*
Fallos:
1 => Faltan variables por especificar
5 => La variable action debe valer "set" o "get"
7 => Fallo al guardar
9 => JSON mal formado en la variable data
*/

/*
Perfects:
1 => Guardado corréctamente
*/

if(!isset($_POST['data']) || !isset($_POST['action'])){
	fail(1);
}

$data = &$_POST['data'];
$action = &$_POST['action'];

if($action !== 'set' && $action !== 'get'){
	fail(5);
}






/*

Una petición retorna un json con lo que se ha pedido
Se pide a partir de un widget y algo concreto del widget

Los widgets se crean dando información sobre qué tipos de datos por nombre guarda y cómo son esos datos para validarlos de ser necesario, límites y más
Además, se puede incluir datos ya predefinidos por forest.tk por lo que al llamar a widget y marcadores, se darán los marcadores de forest.tk y no los del widget (un hipervínculo).

*/

$data_json = json_decode($data, true);
if($data_json === null){
	fail(9);
}


widgetVariablesValido($data_json);

switch(isset($action)?$action:null){
	case 'get':
		// Simple
		$response = getHandler($data_json);
		perfect(json_encode($response));
	break;
	case 'set':
		// Comprobar bloqueos
		$response = setHandler($data_json);
		if($response){
			perfect(json_encode($response));
		}
		else{
			fail(json_encode($response));
		}
	break;
	default:
		// No debería ocurrir nunca
		fail(5);
	break;
}





# --------------------------------------------------------------------------------------------------------------
#
# HANDLERS
#
# --------------------------------------------------------------------------------------------------------------

// Validar una cantidad de widgets determinada por size
// true/false
function widgetVariablesValido(&$widgets){
	foreach($widgets as $widgetID => &$variables){
		if($widgetID !== 'global'){
			if(widgetValido($widgetID) === false){
				unset($widgets[$widgetID]);
			}
		}
	}
}

// Validar un solo widget
function widgetValido(&$widgetID){
	// Únicamente mirar en la base de datos si existe el widget
	// Aprovechar para cargar la información mínima del widget para las futuras preguntas
	global $db;
	return $db->getWidgetPorID($widgetID) ? true : false;
}


// Llamar antes a widgetValidoHandler y variableDeWidgetHandler ya que no se valida aquí
// array con el contenido/false -> fallos al consultar o consulta
function getHandler(&$widgets){
	global $db;
	$respuesta_array = array();
	$response = $db->getVariable($widgets);
	foreach($response as $result){
		$widgetID = $result['IDwidget'] === '-1' ? 'global' : $result['IDwidget']; //global is a invisible widget with id -1
		$respuesta_array[$widgetID][$result['variable']] = $result['value'];
	}
	return $respuesta_array;
}

// Llamar antes a widgetValidoHandler y variableDeWidgetHandler ya que no se valida aquí
// true/false -> fallos al grabar
function setHandler(&$widgets){
	global $db;
	$respuesta_array = array();
	$response = $db->setVariable($widgets);
	foreach($widgets as $widgetID => &$variables_widget){
		foreach($variables_widget as $variable => &$value){
			$respuesta_array[$widgetID][$variable] = $response;
		}
	}
	return $respuesta_array;
}





# --------------------------------------------------------------------------------------------------------------
#
# RESPUESTAS JSON
#
# --------------------------------------------------------------------------------------------------------------

function fail($n){
	responde('FAIL', $n);
	exit;
}

function perfect($n){
	responde('OK', $n);
	exit;
}

function responde($response, $content){
	echo '{"response":"'.$response.'","content":'.$content.'}';
}
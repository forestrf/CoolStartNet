<?php

session_start();
if(!isset($_SESSION['usuario'])){
	exit;
}


require_once 'php/funciones/genericas.php';
require_once 'php/clases/DB.php';

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
Tablas:
	Usuarios
		ID => int
		Nick => string
		Password => string
	Widgets
		ID => int
		Nombre => string
		Variables => json
	Variables:
		ID => int
		IDUsuario => int
		IDWidget => int (-1 = interno, 0..infinito = widgets)
		Variable => string
		Valor => ""/json/string/filtrado segun variable widget
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
		$respuesta = getHandler($data_json);
		perfect(json_encode($respuesta));
	break;
	case 'set':
		// Comprobar bloqueos
		if(setHandler($data_json)){
			perfect(1);
		}
		else{
			fail(7);
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
	global $widgets_array;
	$widgets_array = array();
	
	foreach($widgets as $widget => &$variables){
		$result = widgetValido($widget);
		if($result === false){
			unset($widgets[$widget]);
		}
		else{
			$widgets_array[$widget] = $result;
			
			$widgets_array[$widget]['variables'] = json_decode($result['variables']);
			
			foreach($widgets[$widget] as $variable => &$no_importa){
				$borrar = true;
				foreach($widgets_array[$widget]['variables'] as &$posible_variable){
					if($variable === $posible_variable){
						$borrar = false;
						break;
					}
				}
				if($borrar){
					unset($widgets[$widget][$variable]);
				}
			}
		}
	}
}

// Validar un solo widget
function widgetValido(&$widget){
	// Únicamente mirar en la base de datos si existe el widget
	// Aprovechar para cargar la información mínima del widget para las futuras preguntas
	global $db;
	return $db->getWidget($widget);
}


// Llamar antes a widgetValidoHandler y variableDeWidgetHandler ya que no se valida aquí
// array con el contenido/false -> fallos al consultar o consulta
function getHandler(&$widgets){
	global $db,$widgets_array;
	$respuesta_array = array();
	foreach($widgets as $nombre => &$variables_widget){
		foreach($variables_widget as $variable => $no_importa){
			$respuesta_array[$nombre][$variable] = $db->getVariable($widgets_array[$nombre]['ID'], $variable);
		}
	}
	return $respuesta_array;
}

// Llamar antes a widgetValidoHandler y variableDeWidgetHandler ya que no se valida aquí
// true/false -> fallos al grabar
function setHandler(&$widgets){
	global $db,$widgets_array;
	foreach($widgets as $nombre => &$variables_widget){
		foreach($variables_widget as $variable => &$valor){
			$resp = $db->setVariable($widgets_array[$nombre]['ID'], $variable, $valor);
			/*if($resp === false){
				return false;
			}*/
		}
	}
	return true;
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
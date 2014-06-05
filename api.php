<?php

require_once 'php/funciones/genericas.php';
require_once 'php/clases/DB.php';

// $_GET['size'] => 1..infinito
// $_GET['widget'] => [nombre1,nombre2...]
// $_GET['variable'] => [nombre1,nombre2...]
// $_POST['value'] => [valor1, valor2...]
// $_GET['action'] => get/set

/*
Tablas:
	Usuarios
		ID => int
		Nick => string
		Password => string
	Widgets
		ID => int
		Nombre => string
		Descripción => string
		Variables => json
	Variables:
		ID => int
		IDUsuario => int
		IDWidget => int (-1 = interno, 0..infinito = widgets)
		Variable => string
		Tipo => 1(link)/2(contenedor)
		Valor => ""/json/string/filtrado segun variable widget
*/

/*
Fallos:
1 => Faltan variables por especificar
2 => Los widgets indicados son inválidos o alguno de ellos es inválido
3 => La variable size contiene un valor incorrecto
4 => La variable action contiene set pero no se ha especificado la variable value
5 => La variable action debe valer "set" o "get"
6 => Las variables indicadas no se corresponden con las variables que el widget posée
7 => Fallo al guardar
8 => Fallo al consultar
9 => JSON mal formado en la variable widget
10 => JSON mal formado en la variable variable
11 => JSON mal formado en la variable value
*/

/*
Perfects:
1 => Guardado corréctamente
*/

if(
	!isset($_GET['size']) ||
	!isset($_GET['widget']) ||
	!isset($_GET['variable']) ||
	!isset($_GET['action'])
){
	fail(1);
}

if(!isInteger($_GET['size']) || $_GET['size'] < 1){
	fail(3);
}

if($_GET['action'] !== 'set' && $_GET['action'] !== 'get'){
	fail(5);
}

if($_GET['action'] === 'set' && !isset($_POST['value'])){
	fail(4);
}






/*

Una petición retorna un json con lo que se ha pedido
Se pide a partir de un widget y algo concreto del widget

Los widgets se crean dando información sobre qué tipos de datos por nombre guarda y cómo son esos datos para validarlos de ser necesario, límites y más
Además, se puede incluir datos ya predefinidos por forest.tk por lo que al llamar a widget y marcadores, se darán los marcadores de forest.tk y no los del widget (un hipervínculo).

*/

$widget_json = json_decode($_GET['widget'], false);
if($widget_json === null){
	fail(9);
}
$variable_json = json_decode($_GET['variable'], false);
if($variable_json === null){
	fail(10);
}
if($_GET['action'] === 'set'){
	$value_json = json_decode($_POST['value'], false);
	if($value_json === null){
		fail(11);
	}
}

if(widgetValidoHandler($widget_json, $_GET['size'])){
	if(variableDeWidgetValidoHandler($widget_json, $variable_json, $_GET['size'])){
		switch(isset($_GET['action'])?$_GET['action']:null){
			case 'get':
				// Simple
				$respuesta = getHandler($widget_json, $variable_json, $_GET['size']);
				if($respuesta !== false){
					perfect(json_encode($respuesta));
				}
				else{
					fail(8);
				}
			break;
			case 'set':
				// Comprobar bloqueos
				if(setHandler($widget_json, $variable_json, $value_json, $_GET['size'])){
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
	}
	else{
		fail(6);
	}
}
else{
	fail(2);
}
















# --------------------------------------------------------------------------------------------------------------
#
# HANDLERS
#
# --------------------------------------------------------------------------------------------------------------

// Validar una cantidad de widgets determinada por size
// true/false
function widgetValidoHandler(&$widgets, $size){
	if(count($widgets) !== (int)$size){
		return false;
	}
	foreach($widgets as &$widget){
		if(!widgetValido($widget)){
			return false;
		}
	}
	return true;
}

// Validar un solo widget
function widgetValido(&$widget){
	// Únicamente mirar en la base de datos si existe el widget
	// Aprovechar para cargar la información mínima del widget para las futuras preguntas
	return true;
}

// Antes deben de validarse los widgets. Es necesario llamar antes a widgetValidoHandler
// true/false
function variableDeWidgetValidoHandler(&$widgets, &$variables, $size){
	if(count($variables) !== (int)$size){
		return false;
	}
	foreach($widgets as &$widget){
		foreach($variables as &$variable){
			if(!variableDeWidgetValido($widget, $variable)){
				return false;
			}
		}
	}
	return true;
}

// Variable pertenece al widget
// true/false
function variableDeWidgetValido(&$widget, &$variable){
	// Únicamente mirar en la base de datos si el widget tiene esa variable
	return true;
}

// Llamar antes a widgetValidoHandler y variableDeWidgetHandler ya que no se valida aquí
// array con el contenido/false -> fallos al consultar o consulta
function getHandler(&$widgets, &$variables, $size){}

// Llamar antes a widgetValidoHandler y variableDeWidgetHandler ya que no se valida aquí
// true/false -> fallos al grabar
function setHandler(&$widgets, &$variables, &$valores, $size){}





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
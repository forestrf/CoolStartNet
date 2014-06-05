<?php

require_once 'php/funciones/genericas.php';

// $_GET['size'] => 1..infinito
// $_GET['widget'] => nombre/[nombre1,nombre2...]
// $_GET['variable'] => nombre/[nombre1,nombre2...]
// $_POST['value'] => valor/[valor1, valor2...]
// $_GET['action'] => get/set

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

if(widgetValidoHandler($_GET['widget'])){
	if(variableDeWidgetHandler($_GET['widget'], $_GET['variable'])){
		switch(isset($_GET['action'])?$_GET['action']:null){
			case 'get':
				// Simple
				$respuesta = getHandler($_GET['widget'], $_GET['variable']);
				if($respuesta !== false){
					perfect(json_encode($respuesta));
				}
				else{
					fail(8);
				}
			break;
			case 'set':
				// Comprobar bloqueos
				if(setHandler($_GET['widget'], $_GET['variable'], $_POST['value'])){
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

// true/false
function widgetValidoHandler($widgets){return true;}

// true/false
function variableDeWidgetHandler($widgets, $variables){return true;}

// Llamar antes a widgetValidoHandler y variableDeWidgetHandler ya que no se valida aquí
// array con el contenido/false -> fallos al consultar o consulta
function getHandler($widgets, $variables){}

// Llamar antes a widgetValidoHandler y variableDeWidgetHandler ya que no se valida aquí
// true/false -> fallos al grabar
function setHandler($widgets, $variables, $valores){}





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
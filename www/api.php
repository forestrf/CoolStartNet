<?php

# This file is part of MyHomePage.
#
#	 MyHomePage is free software: you can redistribute it and/or modify
#	 it under the terms of the GNU Affero General Public License as published by
#	 the Free Software Foundation, either version 3 of the License, or
#	 (at your option) any later version.
#
#	 MyHomePage is distributed in the hope that it will be useful,
#	 but WITHOUT ANY WARRANTY; without even the implied warranty of
#	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#	 GNU Affero General Public License for more details.
#
#	 You should have received a copy of the GNU Affero General Public License
#	 along with MyHomePage.  If not, see <http://www.gnu.org/licenses/>.


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
		variable => value
	)
)
$_GET['action'] => get/set
*/

/*
Fails:
1 => There are no specified variables
5 => The variable "action" must be "set" o "get"
7 => Problem saving the data
9 => Bad JSON in the "data" variable
*/

/*
Perfects:
1 => Saved perfectly
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

A request returns a JSON with all the requested data
The request needs a widget ID / the text "public" and a variable

*/

$data_json = json_decode($data, true);
if($data_json === null){
	fail(9);
}


widget_variables_valid($data_json);

switch(isset($action)?$action:null){
	case 'get':
		// Simple
		$response = getHandler($data_json);
		perfect(json_encode($response));
	break;
	case 'set':
		// Check blocks
		$response = setHandler($data_json);
		if($response){
			perfect(json_encode($response));
		}
		else{
			fail(json_encode($response));
		}
	break;
	default:
		// Never should occur
		fail(5);
	break;
}





# --------------------------------------------------------------------------------------------------------------
#
# HANDLERS
#
# --------------------------------------------------------------------------------------------------------------

// Validate a widget list
// returns true or false
function widget_variables_valid(&$widgets){
	global $db;
	foreach($widgets as $widgetID => &$variables){
		if($widgetID !== 'global'){
			if(!$db->get_widget_by_ID($widgetID)){
				unset($widgets[$widgetID]);
			}
		}
	}
}


// Call before the function widget_variables_valid()
// returns an array with the content or false -> read failure
function getHandler(&$widgets){
	global $db;
	$array_response = array();
	$response = $db->get_variable($widgets);
	foreach($response as $result){
		$widgetID = $result['IDwidget'] === '-1' ? 'global' : $result['IDwidget']; //global is a invisible widget with id -1
		$array_response[$widgetID][$result['variable']] = $result['value'];
	}
	return $array_response;
}

// Call before the function widget_variables_valid()
// returns true or false -> write failure
function setHandler(&$widgets){
	global $db;
	$array_response = array();
	$response = $db->set_variable($widgets);
	foreach($widgets as $widgetID => &$variables_widget){
		foreach($variables_widget as $variable => &$value){
			$array_response[$widgetID][$variable] = $response;
		}
	}
	return $array_response;
}





# --------------------------------------------------------------------------------------------------------------
#
# JSON RESPONSES
#
# --------------------------------------------------------------------------------------------------------------

function fail($n){
	respond('FAIL', $n);
	exit;
}

function perfect($n){
	respond('OK', $n);
	exit;
}

function respond($response, $content){
	echo '{"response":"'.$response.'","content":'.$content.'}';
}
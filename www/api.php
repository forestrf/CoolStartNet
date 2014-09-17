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

require_once 'php/functions/generic.php';
$db = open_db_session();
if(!isset($_SESSION['user'])){
	exit;
}

insert_nocache_headers();

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

if(
	$action !== 'set' &&
	$action !== 'get' &&
	$action !== 'del' &&
	$action !== 'delall' &&
	$action !== 'check'
){
	fail(5);
}


// Solo permitir default sin login en caso de get o check
user_check_access($action === 'get' || $action === 'check');



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
		$response = getHandler($data_json);
		perfect(json_encode($response));
	break;
	case 'set':
		$response = setHandler($data_json);
		if(is_array($response) && count($response) > 0){
			perfect(json_encode($response));
		}
		else{
			fail(json_encode($response));
		}
	break;
	case 'del':
		$response = delHandler($data_json);
		if(is_array($response) && count($response) > 0){
			perfect(json_encode($response));
		}
		else{
			fail(json_encode($response));
		}
	break;
	case 'delall':
		$response = delallHandler($data_json);
		if(is_array($response) && count($response) > 0){
			perfect(json_encode($response));
		}
		else{
			fail(json_encode($response));
		}
	break;
	case 'check':
		$response = checkHandler($data_json);
		perfect(json_encode($response));
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

$hashes = array();

// Validate a widget list
// returns true or false
function widget_variables_valid(&$widgets){
	global $db, $hashes;
	foreach($widgets as $widgetID => &$variables){
		if($widgetID != -1){
			widget_remove_secret($widgetID, $widgetID_real, $secret);
			if(!validateWidget($widgetID_real, $secret, $hash)){
				unset($widgets[$widgetID]);
			}
			else{
				if(!$db->get_widget_by_ID($widgetID_real)){
					unset($widgets[$widgetID]);
				}
				$hashes[$widgetID_real] = $widgetID;
			}
		}
	}
}

function widget_add_secret(&$widgetID, &$secret){
	return $widgetID . '-' . $secret;
}

function widget_remove_secret(&$widgetSecret, &$widgetID, &$secret){
	$secret = substr($widgetSecret, strpos($widgetSecret, '-')+1);
	$widgetID = substr($widgetSecret, 0, strpos($widgetSecret, '-'));
}

// Call before the function widget_variables_valid()
function getHandler(&$widgets){
	global $db, $hashes;
	$array_response = array();
	$response = $db->get_variable($widgets);
	foreach($response as &$result){
		//global is a invisible widget with id -1
		$array_response[$result['IDwidget'] === '-1' ? '-1' : $hashes[$result['IDwidget']]][$result['variable']] = $result['value'];
	}
	return $array_response;
}

// Call before the function widget_variables_valid()
function checkHandler(&$widgets){
	global $db, $hashes;
	$array_response = array();
	$response = $db->check_variable($widgets);
	foreach($response as &$result){
		//global is a invisible widget with id -1
		$array_response[$result['IDwidget'] === '-1' ? '-1' : $hashes[$result['IDwidget']]][$result['variable']] = 1;
	}
	return $array_response;
}

// Call before the function widget_variables_valid()
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

// Call before the function widget_variables_valid()
function delHandler(&$widgets){
	global $db, $hashes;
	$array_response = array();
	$response = $db->del_variable($widgets);
	foreach($widgets as $widgetID => &$variables_widget){
		foreach($variables_widget as $variable => &$value){
			$array_response[$widgetID][$variable] = $response;
		}
	}
	return $array_response;
}

// Call before the function widget_variables_valid()
function delallHandler(&$widgets){
	global $db, $hashes;
	$array_response = array();
	$response = $db->delall_variable($widgets, true);
	foreach($widgets as $widgetID => &$variables_widget){
		foreach($variables_widget as $variable => &$value){
			$array_response[$widgetID][$variable] = $response;
		}
	}
	return $array_response;
}



function validateWidget($widgetID, $secret, &$hash){
	$hash = hash_api($_SESSION['user']['RND'], $widgetID, PASSWORD_TOKEN_API);
	return $secret === $hash;
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
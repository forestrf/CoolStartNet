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

$actions = array(
	0 => 'get',
	1 => 'check',
	2 => 'set',
	3 => 'del',
	4 => 'delall'
);

require_once __DIR__ . '/php/defaults.php';
require_once __DIR__ . '/php/functions/generic.php';

$db = open_db_session();
if (!G::$SESSION->exists()) {
	// User can't save or delete, only read
	unset($actions[2]);
	unset($actions[3]);
	unset($actions[4]);
}

insert_nocache_headers();

/*
$_GET['data'] => json(
	widget => (
		hash => '' / null
		keys => (
			variable => value
		)
	)
)
$_GET['action'] => get/set...
*/

if (!isset($_POST['data']) || !isset($_POST['action'])) {
	fail(1);
}

$data = &$_POST['data'];
$action = &$_POST['action'];

if (!in_array($action, $actions)) {
	fail(5);
}



/*

A request returns a JSON with all the requested data
The request needs a widget ID / the text "public" and a variable

*/

$data_json = json_decode($data, true);
if ($data_json === null) {
	fail(9);
}


widget_variables_valid($data_json);
if ($db->is_away()) fail(11);

switch (isset($action)?$action:null) {
	case 'get':
		$response = getHandler($data_json);
		perfect(json_encode($response));
	break;
	case 'set':
		$response = setHandler($data_json);
		if (is_array($response) && count($response) > 0) {
			perfect(json_encode($response));
		} else {
			fail(json_encode($response));
		}
	break;
	case 'del':
		$response = delHandler($data_json);
		if (is_array($response) && count($response) > 0) {
			perfect(json_encode($response));
		} else {
			fail(json_encode($response));
		}
	break;
	case 'delall':
		$response = delallHandler($data_json);
		if (is_array($response) && count($response) > 0) {
			perfect(json_encode($response));
		} else {
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

// Validate a widget list. removing the invalid widgets
function widget_variables_valid(&$widgets) {
	global $db;
	foreach ($widgets as $widgetID => &$variables)
		if ($widgetID != DB::GLOBAL_WIDGET)
			if (!validateWidget($widgetID, $variables['hash']))
				unset($widgets[$widgetID]);
	
	$ids = array_keys($widgets);
	$valid_ids = $db->exists_widgets($ids);
	
	foreach ($widgets as $widgetID => &$variables)
		if (!in_array($widgetID, $valid_ids))
			unset($widgets[$widgetID]);
}

// Call before the function widget_variables_valid()
function getHandler(&$widgets) {
	global $db;
	$array_response = array();
	$response = $db->get_variable($widgets);
	foreach ($response as &$result) {
		$array_response[$result['IDwidget']][$result['variable']] = $result['value'];
	}
	return $array_response;
}

// Call before the function widget_variables_valid()
function checkHandler(&$widgets) {
	global $db;
	$array_response = array();
	$response = $db->check_variable($widgets);
	foreach ($response as &$result) {
		$array_response[$result['IDwidget']][$result['variable']] = true;
	}
	return $array_response;
}

// Call before the function widget_variables_valid()
function setHandler(&$widgets) {
	global $db;
	$array_response = array();
	$response = $db->set_variable($widgets);
	foreach ($widgets as $widgetID => &$variables_widget) {
		foreach ($variables_widget['keys'] as $variable => &$value) {
			$array_response[$widgetID][$variable] = $response;
		}
	}
	return $array_response;
}

// Call before the function widget_variables_valid()
function delHandler(&$widgets) {
	global $db;
	$array_response = array();
	$response = $db->del_variable($widgets);
	foreach ($widgets as $widgetID => &$variables_widget) {
		foreach ($variables_widget['keys'] as $variable => &$value) {
			$array_response[$widgetID][$variable] = $response;
		}
	}
	return $array_response;
}

// Call before the function widget_variables_valid()
function delallHandler(&$widgets) {
	global $db;
	$array_response = array();
	$response = $db->delall_variable($widgets, true);
	foreach ($widgets as $widgetID => &$variables_widget) {
		foreach ($variables_widget['keys'] as $variable => &$value) {
			$array_response[$widgetID][$variable] = $response;
		}
	}
	return $array_response;
}



function validateWidget($widgetID, $hash) {
	$hash_generated = hash_api(G::$SESSION->get_user_random(), $widgetID, PASSWORD_TOKEN_API);
	return $hash_generated === $hash;
}




# --------------------------------------------------------------------------------------------------------------
#
# JSON RESPONSES
#
# --------------------------------------------------------------------------------------------------------------

function fail($n) {
	$fails = array(
		1 => 'Incomplete request (data and action are required)',
		5 => 'Invalid action',
		7 => 'Problem saving the data',
		9 => 'Data not valid. JSON parse fail',
		11 => 'Database unreachable'
	);
	respond('FAIL', '"' . $fails[$n] . '"');
	exit;
}

function perfect($n) {
	respond('OK', $n);
	exit;
}

function respond($response, $content) {
	echo '{"response":"'.$response.'","content":'.$content.'}';
}
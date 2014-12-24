<?php
require_once __DIR__.'/php/defaults.php';

# option === function name

$options = array(	
	'global-list'                        => 0 // Get
	,'global-list-search'                => 0 // Get
	,'user-created-list'                 => 0 // Get
	,'user-created-version-list'         => 0 // Get
	,'user-created-version-info'         => 0 // Get
	,'user-created-version-files-list'   => 0 // Get
	,'user-using-list'                   => 0 // Get
	
	,'user-created-create'               => 1 // Set
	,'user-created-version-add'          => 1 // Set
	,'user-created-version-remove'       => 1 // Set
	,'user-created-version-info-edit'    => 1 // Set
	,'user-created-version-files-add'    => 1 // Set
	,'user-created-version-files-edit'   => 1 // Set
	,'user-created-version-files-remove' => 1 // Set
	,'user-created-remove'               => 1 // Set
	,'user-using-add'                    => 1 // Set
	,'user-using-remove'                 => 1 // Set
);




// Validate GET option

if (isset($_REQUEST['action'])) {
	$action = &$_REQUEST['action'];
	if (!isset($options[$action])) {
		end_fail('Invalid action specified');
	}
} else {
	end_fail('Not action specified');
}



// check login

require_once __DIR__.'/php/functions/generic.php';

$db = open_db_session();

if(!user_check_access(false, true)){
	end_fail('User not logged in');
}



// Launch option function

$action = str_replace('-', '_', $action);
$action($db);





/////////////////////////////////////////////////////////
//
// FUNCTIONS OF THE OPTIONS
//
/////////////////////////////////////////////////////////

function global_list(&$db) {
	$widgets = $db->get_availabe_widgets_user();
	$result = generate_widget_array($widgets);
	end_ok($result);
}

function user_using_list(&$db) {
	$widgets = $db->get_widgets_user();
	$result = generate_widget_array($widgets);
	end_ok($result);
}

function user_created_list(&$db) {
	$widgets = $db->get_widgets_user_owns();
	$result = generate_widget_array($widgets);
	end_ok($result);
}

function global_list_search(&$db) {
	$widgets = $db->get_availabe_widgets_user();
	$result = generate_widget_array($widgets);
	end_ok($result);
}

/*
,'global-list-search'                => 0 // Get
,'user-created-version-list'         => 0 // Get
,'user-created-version-info'         => 0 // Get
,'user-created-version-files-list'   => 0 // Get


,'user-created-create'               => 1 // Set
,'user-created-version-add'          => 1 // Set
,'user-created-version-remove'       => 1 // Set
,'user-created-version-info-edit'    => 1 // Set
,'user-created-version-files-add'    => 1 // Set
,'user-created-version-files-edit'   => 1 // Set
,'user-created-version-files-remove' => 1 // Set
,'user-created-remove'               => 1 // Set
,'user-using-add'                    => 1 // Set
,'user-using-remove'                 => 1 // Set
*/
















/////////////////////////////////////////////////////////
//
// SECONDARY FUNCTIONS
//
/////////////////////////////////////////////////////////

function generate_widget_array(&$widgets) {
	$result = array();

	if ($widgets) {
		foreach ($widgets as &$widget) {
			$result[] = return_widget_array_element($widget);
		}
	}

	return $result;
}

function return_widget_array_element(&$widget) {
	return array(
		'IDwidget'    => $widget['IDwidget'],
		'name'        => $widget['name'],
		'description' => isset($widget['description']) ? $widget['description'] : 'No description available.',
		'image'       => '',
		//'token'       => hash_ipa($_SESSION['user']['RND'], $widget['IDwidget'], PASSWORD_TOKEN_IPA),
		'version'     => isset($widget['version']) ? $widget['version'] : '',
		'autoupdate'  => isset($widget['autoupdate']) ? $widget['autoupdate'] : ''
	);
}







function end_ok(&$array_response) {
	$response = array (
		'status' => 'OK'
		,'response' => $array_response
	);
	echo json_encode($response);
	exit;
}

function end_fail($txt) {
	echo '{"status":"FAIL","problem":"'.$txt.'"}';
	exit;
}

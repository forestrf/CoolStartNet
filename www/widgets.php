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
	,'user-created-update'               => 1 // Set
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

// -- GET -- //

function global_list(DB &$db) {
	$widgets = $db->get_availabe_widgets_user();
	$result = generate_widget_array($widgets);
	end_ok($result);
}

function user_using_list(DB &$db) {
	$widgets = $db->get_widgets_user();
	$result = generate_widget_array($widgets);
	end_ok($result);
}

function user_created_list(DB &$db) {
	$widgets = $db->get_widgets_user_owns();
	$result = generate_widget_array($widgets);
	end_ok($result);
}

function global_list_search(DB &$db) {
	if (!isset($_POST['search']) || $_POST['search'] === '') return global_list($db);
	$widgets = $db->search_availabe_widgets_user($_POST['search']);
	$result = generate_widget_array($widgets);
	end_ok($result);
}

/*
,'user-created-version-list'         => 0 // Get
,'user-created-version-info'         => 0 // Get
,'user-created-version-files-list'   => 0 // Get


,'user-created-create'               => 1 // Set
,'user-created-update'               => 1 // Set
,'user-created-version-add'          => 1 // Set
,'user-created-version-remove'       => 1 // Set
,'user-created-version-info-edit'    => 1 // Set
,'user-created-version-files-add'    => 1 // Set
,'user-created-version-files-edit'   => 1 // Set
,'user-created-version-files-remove' => 1 // Set
,'user-created-remove'               => 1 // Set
*/

// -- SET -- //
// Sets are not secure

function user_using_add(DB &$db) {
	$result = $db->add_using_widget_user($_POST['IDwidget']);
	end_ok($result);
}

function user_using_remove(DB &$db) {
	$result = $db->remove_using_widget_user($_POST['IDwidget']);
	end_ok($result);
}

function user_created_create(DB &$db) {
	if (isset($_POST['name']) && $_POST['name'] !== '' && preg_match('@[a-zA-Z0-9 ]{1,30}@', $_POST['name'])) {
		$result = $db->create_widget($_POST['name']);
		end_ok($result);
	} else {
		end_fail('invalid widget name');
	}
}

// Incomplete
function user_created_update(DB &$db) {
	if (isset($_POST['IDwidget'])
		&& isset($_POST['name'])
		&& isset($_POST['description'])
		&& isset($_POST['fulldescription'])
		&& isset($_FILES['image'])
	) {
		$widget = $db->get_widget_by_ID($_POST['IDwidget']);
		
		$widget_data = array(
			'name'            => isset_and_default($_POST, 'name', $widget['name']),
			'description'     => isset_and_default($_POST, 'description', $widget['description']),
			'fulldescription' => isset_and_default($_POST, 'fulldescription', $widget['fulldescription'])
		);
		
		if (isset($_FILES['image'])) {
			$widget_data['preview'] = 'preview.jpg';
			file_upload_widget_version($db, $_POST['IDwidget'], DB::GLOBAL_VERSION, $_FILES['image'], 'preview.jpg');
		}
		
		$db->set_widget_data($_POST['IDwidget'], $widget_data);
	}
}














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
		'IDwidget'        => $widget['IDwidget'],
		'name'            => $widget['name'],
		'description'     => isset_and_default($widget, 'description', 'No description available.'),
		'fulldescription' => isset_and_default($widget, 'fulldescription', 'No full description available.'),
		'preview'         => 'preview.jpg',
		'images'          => json_decode(isset_and_default($widget, 'images', '[]')),
		//'token'           => hash_ipa(G::$SESSION->get_user_random(), $widget['IDwidget'], PASSWORD_TOKEN_IPA),
		'version'         => isset_and_default($widget, 'version', ''),
		'autoupdate'      => isset_and_default($widget, 'autoupdate', ''),
		'inuse'           => isset_and_default($widget, 'IDuser', false) !== false ? true : false,
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
	$response = array (
		'status' => 'FAIL'
		,'problem' => $txt
	);
	echo json_encode($response);
	exit;
}

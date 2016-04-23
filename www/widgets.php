<?php
require_once __DIR__.'/php/defaults.php';

# option === function name

$options = array(	
	'global-list'                => 0 // Get
	,'global-list-search'        => 0 // Get
	,'user-created-list'         => 0 // Get
	,'user-created-files-list'   => 0 // Get
	,'user-using-list'           => 0 // Get
	
	,'user-created-create'       => 1 // Set
	,'user-created-update'       => 1 // Set
	,'user-created-files-add'    => 1 // Set
	,'user-created-files-edit'   => 1 // Set
	,'user-created-files-remove' => 1 // Set
	,'user-created-remove'       => 1 // Set
	,'user-using-add'            => 1 // Set
	,'user-using-remove'         => 1 // Set
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

function user_created_files_list(DB &$db) {
	if (!isset($_POST['widgetID']) || $_POST['widgetID'] === '') end_fail();
	$widgets = $db->get_widget_contents($_POST['widgetID']);
	$result = generate_widget_list_array($widgets);
	end_ok($result);
}

function global_list_search(DB &$db) {
	if (!isset($_POST['search']) || $_POST['search'] === '') return global_list($db);
	$widgets = $db->search_availabe_widgets_user($_POST['search']);
	$result = generate_widget_array($widgets);
	end_ok($result);
}

/*


,'user-created-create'       => 1 // Set
,'user-created-update'       => 1 // Set
,'user-created-files-add'    => 1 // Set
,'user-created-files-edit'   => 1 // Set
,'user-created-files-remove' => 1 // Set
,'user-created-remove'       => 1 // Set
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
			file_upload_widget($db, $_POST['IDwidget'], $_FILES['image'], 'preview.jpg');
		}
		
		$db->set_widget_data($_POST['IDwidget'], $widget_data);
		
		end_ok('Widget updated');
	}
}

function user_created_files_add(DB &$db) {
	if (isset($_POST['IDwidget'])
		&& isset($_FILES['files'])
	){
		//var_dump($_FILES);
		$nFiles = count($_FILES['files']['name']);
		for ($i = 0; $i < $nFiles; $i++) {
			$file = array(
				'name' => $_FILES['files']['name'][$i],
				'tmp_name' => $_FILES['files']['tmp_name'][$i],
				'error' => $_FILES['files']['error'][$i],
				'size' => $_FILES['files']['size'][$i]
			);
			file_upload_widget($db, $_POST['IDwidget'], $file, $file['name']);
		}
		end_ok('files uploaded');
	}
}

function user_created_files_edit(DB &$db) {
	if (isset($_POST['IDwidget'])
		&& isset($_FILES['files'])
	){
		//var_dump($_FILES);
		$nFiles = count($_FILES['files']['name']);
		for ($i = 0; $i < $nFiles; $i++) {
			$file = array(
				'name' => $_FILES['files']['name'][$i],
				'tmp_name' => $_FILES['files']['tmp_name'][$i],
				'error' => $_FILES['files']['error'][$i],
				'size' => $_FILES['files']['size'][$i]
			);
			file_upload_widget($db, $_POST['IDwidget'], $file, $file['name']);
		}
		end_ok('files uploaded');
	}
	end_ok("LOOOL");
}














/////////////////////////////////////////////////////////
//
// SECONDARY FUNCTIONS
//
/////////////////////////////////////////////////////////

function generate_widget_array(&$widgets) {
	$result = array();

	foreach ($widgets as &$widget) {
		$result[] = array(
			'IDwidget'        => $widget['IDwidget'],
			'name'            => $widget['name'],
			'description'     => isset_and_default($widget, 'description', 'No description available.'),
			'fulldescription' => isset_and_default($widget, 'fulldescription', 'No full description available.'),
			'preview'         => 'preview.jpg',
			'images'          => json_decode(isset_and_default($widget, 'images', '[]')),
			//'token'           => hash_ipa(G::$SESSION->get_user_random(), $widget['IDwidget'], PASSWORD_TOKEN_IPA),
			'inuse'           => isset_and_default($widget, 'IDuser', false) !== false ? true : false,
		);
	}

	return $result;
}

function generate_widget_list_array(&$widget_contents) {
	$result = array();

	foreach ($widget_contents as &$Content) {
		$result[] = array(
			'name' => $Content['name']
		);
	}

	return $result;
}



/////////////////////////////////////////////////////////
//
// END FUNCTIONS
//
/////////////////////////////////////////////////////////

function end_ok($response) {
	$response = array (
		'status' => 'OK'
		,'response' => $response
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

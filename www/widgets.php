<?php
require_once __DIR__.'/php/defaults.php';

# option === function name

$options = array(	
	'user-created-create'                => 0
	,'user-created-list'                 => 0
	,'user-created-version-add'          => 0
	,'user-created-version-list'         => 0
	,'user-created-version-remove'       => 0
	,'user-created-version-info'         => 0
	,'user-created-version-info-edit'    => 0
	,'user-created-version-files-list'   => 0
	,'user-created-version-files-add'    => 0
	,'user-created-version-files-edit'   => 0
	,'user-created-version-files-remove' => 0
	,'user-created-remove'               => 0
	,'user-using-list'                   => 0
	,'user-using-add'                    => 0
	,'user-using-remove'                 => 0
	,'global-list'                       => 0
	,'global-list-search'                => 0
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

function global_list(&$db){
	$widgets = $db->get_availabe_widgets_user();
	
	$result = array();

	if($widgets){
		foreach($widgets as &$widget){
			$result[] = array(
				'ID'          => $widget['ID'],
				'name'        => $widget['name'],
				'description' => 'Example description.',
				'token'       => hash_ipa($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_IPA)
			);
		}
	}
	
	end_ok($result);
}

function user_using_list(&$db){
	$widgets = $db->get_widgets_user();
	
	$result = array();

	if($widgets){
		foreach($widgets as &$widget){
			$result[] = array(
				'ID'          => $widget['ID'],
				'name'        => $widget['name'],
				'description' => 'Example description.',
				'token'       => hash_ipa($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_IPA),
				'version'     => $widget['version'],
				'autoupdate'  => $widget['autoupdate']
			);
		}
	}
	
	end_ok($result);
}







function end_ok(&$array_response){
	$response = array(
		'status' => 'OK'
		,'response' => $array_response
	);
	echo json_encode($response);
	exit;
}

function end_fail($txt){
	echo '{"status":"FAIL","problem":"'.$txt.'"}';
	exit;
}
<style>
body, a {
	background-color:#000;
	color:#fff;
}
.ok {
	color:#0f0;
}
.fail {
	color:#f00;
}
.info {
	color:#ff0;
}
.query {
	color:#0ff;
}
</style>
Select steep:<p>

<a href="?steep=install-db">Install database</a><br>
<a href="?steep=update-db">Update database</a><br>

<a href="?steep=install-widgets">Install/update widgets from widgets folder</a><br>

<a href="?steep=create-default-user">Create user DEFAULT and configure it</a><br>
<a href="?steep=create-test-user">Create user TEST and configure it</a><br>

<pre>
<?php

require_once '../php/functions/generic.php';

$steep = isset($_GET['steep']) ? $_GET['steep'] : '';

switch ($steep) {
	case 'install-db':
		delete_apc_cache();
		
		$sql_path = '../../sql/db.sql';
		$db_instructions = file_get_contents($sql_path);
		
		require_once '../php/lib/DB.php';
		
		$db = new DB();
		$db->debug_mode(true);
		
		$db->create_tables($db_instructions);
	break;
	
	// It needs the default widgets
	case 'create-default-user':
		delete_apc_cache();
		
		require_once '../php/lib/DB.php';
		
		$db = new DB();
		$db->debug_mode(true);
		
		$db->delete_user(DEFAULT_USER_NICK);
		$db->create_new_user(DEFAULT_USER_NICK, DEFAULT_USER_PASSWORD, '', $validation);
		
		$userID = $db->LAST_MYSQL_ID;
		var_dump($userID);
		
		$db->validate_new_user(DEFAULT_USER_NICK, $validation);
		
		$db->set_user_id($userID);
		
		$widgets = array(
			'global' => array(
				'keys' => array(
					'background_images' => array(
						array('img/bg/caldeum_by_tituslunter-d5qinlq.jpg','http://tituslunter.deviantart.com/art/Caldeum-346871294'),
						array('img/bg/stronghold_by_tituslunter-d5pno2d.jpg','http://tituslunter.deviantart.com/art/Stronghold-345425557'),
						array('img/bg/view_afternoon_in_the_future_by_campanoo-d6dvcta.jpg','http://campanoo.deviantart.com/art/view-afternoon-in-the-future-386095006')
					)
				)
			),
			'Basic clock' => array(
				'keys' => array(
					'pos' => array('left'=>'76.7187','top'=>'0.814901','width'=>'20','height'=>'20')
				)
			),
			'Background image' => array('keys' => array()),
			'Coolstart Title' => array('keys' => array()),
			'Login window' => array('keys' => array())
		);
		
		$widgets_variables = array();
		
		foreach ($widgets as $name => &$elem) {
			$widget = $db->get_widget($name);
			
			if ($widget['IDwidget'] != DB::GLOBAL_WIDGET) {
				$db->add_using_widget_user($widget['IDwidget']);
			}
			
			$widgets_variables[$widget['IDwidget']] = &$elem;
		}

		var_dump($widgets_variables);
		$db->set_variable($widgets_variables);
	break;




	case 'create-test-user':
		delete_apc_cache();
		
		require_once '../php/lib/DB.php';
		
		$db = new DB();
		$db->debug_mode(true);
		
		$db->delete_user('testing');
		$db->create_new_user('testing', 'testing', 'testing@testing.testing', $validation);
		
		$userID = $db->LAST_MYSQL_ID;
		var_dump($userID);
		
		$db->validate_new_user('testing', $validation);
	break;





	case 'install-widgets':
		delete_apc_cache();
		
		// search inside the widgets folder and add all the widgets to the user with id -1.
		// Admin user must be able to edit this afterwards
		
		$widgets_path = '../../widgets/';
		
		$default_static_files = array('preview.jpg');
		
		$widgets = array();
		
		$d = dir($widgets_path);
		while (false !== ($entry = filter_directory($d, true, false))) {
			if (is_dir($widgets_path.$entry)) {
				$widget_path = $widgets_path . $entry . '/';
				
				$info = file_get_contents($widget_path . 'info.txt');
				$info = preg_replace('@/\*.*?\*/@', '', $info); // Remove comments /* blabla */
				//var_dump($info);
				$widget = json_decode($info, true);
				
				if (strlen($widget['name']) > 30 ) {
					$widget['name'] = substr($entry, 0, 30);
				}
				
				$widget['files'] = array();
				
				$widget_d = dir($widget_path);
				
				while (false !== ($entry = filter_directory($widget_d, false, true))) {
					// The default js will be main.js
					$widget['files'][] = array(
						'name' => $entry,
						'path' => $widget_d->path . $entry
					);
				}
				$widget_d->close();
				
				if (!isset($widget['staticfiles'])) $widget['staticfiles'] = array();
				
				$widget['staticfiles'] = array_merge($widget['staticfiles'], $default_static_files);
				
				$widgets[] = $widget;
			}
		}
		$d->close();
		
		
		
		// We have the list with the widgets, paths and names
		//print_r($widgets);
		
		require_once '../php/lib/DB.php';
		
		$db = new DB();
		$db->set_user_id(GLOBAL_USER_ID);
		$db->debug_mode(true);
		
		foreach ($widgets as &$widget) {
			if ($db->create_widget($widget['name'])) {
				$IDwidget = $db->LAST_MYSQL_ID;
			} else {
				$IDwidget = $db->get_widget($widget['name']);
				$IDwidget = $IDwidget['IDwidget'];
			}
			var_dump($IDwidget);
			
			$data = array(
				'description' => isset_and_default($widget, 'description', ''),
				'fulldescription' => isset_and_default($widget, 'fulldescription', ''),
				'images' => isset_and_default($widget, 'images', '')
			);
			$db->set_widget_data($IDwidget, $data);
			
			foreach ($widget['files'] as &$file) {
				$file_contents = file_get_contents($file['path']);
				//$v = in_array($file['name'], $widget['staticfiles']) ? -1 : $version;
				$db->upload_widget_file($IDwidget, $file['name'], $file_contents);
			}
			//$db->set_widget_creation_date($id, $widget['date']);
			//$db->set_widget_autor();
			//$db->set_widget_visibility($IDwidget, $widget['visible']);
		}
		
	break;
}

function delete_apc_cache() {
	$info = apc_cache_info('user');
	foreach ($info['cache_list'] as $obj) {
	    apc_delete($obj['info']);
	    print 'Deleted: ' . $obj['info'] . PHP_EOL;
	}
}

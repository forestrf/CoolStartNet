Select steep:<p>

<a href="?steep=install-db">Install database</a><br>
<a href="?steep=update-db">Update database</a><br>

<a href="?steep=install-widgets">Install default widgets</a><br>
<a href="?steep=update-widgets">Update default widgets</a><br>

<a href="?steep=create-default-user">Create user DEFAULT and configure it</a><br>

<pre>
<?php

$steep = isset($_GET['steep']) ? $_GET['steep'] : '';

switch ($steep) {
	case 'install-db':
		$sql_path = '../../sql/db.sql';
		$db_instructions = file_get_contents($sql_path);
		
		require_once '../php/lib/DB.php';
		
		$db = new DB();
		
		$db->create_tables($db_instructions);
	break;
	
	// It needs the default widgets
	case 'create-default-user':
		require_once '../php/lib/DB.php';
		
		$db = new DB();
		
		$db->delete_user(DEFAULT_USER_NICK);
		$db->create_new_user(DEFAULT_USER_NICK, DEFAULT_USER_PASSWORD, '', $validation);
		
		$userID = $db->LAST_MYSQL_ID;
		var_dump($userID);
		
		$db->validate_new_user(DEFAULT_USER_NICK, $validation);
		
		$db->set_user_id($userID);
		
		$widgets = array(
			'global' => array(
				'background_images' => array(array('img/bg/homepage.jpg','#000000','e','m','n'))
			),
			'Background image' => array(),
			'Basic clock' => array(
				'pos' => array('left'=>'76.7187','top'=>'0.814901','width'=>'20','height'=>'20')
			),
			'Coolstart Title' => array(),
			'Login window' => array()
		);
		
		$widgets_variables = array();
		
		foreach ($widgets as $name => &$elem) {
			$widget = $db->get_widget($name);
			
			if ($widget['ID'] != '-1') {
				$db->add_using_widget_user($widget['ID']);
			}
			
			$widgets_variables[$widget['ID']] = &$elem;
			var_dump($widget);
		}

		$db->set_variable($widgets_variables);
		
		//$db->add_using_widget_user($widgetID);
	break;






	case 'install-widgets':
		// search inside the widgets folder and add all the widgets to the user with id -1.
		// Admin user must be able to edit this afterwards
		
		$widgets_path = '../../widgets/';
		
		$widgets = array();
		
		$d = dir($widgets_path);
		while (false !== ($entry = filter_directory($d, true, false))) {
			if ($entry === '.' || $entry === '..') continue;
			
			if (is_dir($widgets_path.$entry)) {
				$widget_path = $widgets_path . $entry . '/';
				
				$info = file_get_contents($widget_path . 'info.txt');
				$info = preg_replace('@/\*.*?\*/@', '', $info); // Remove comments /* blabla */
				//var_dump($info);
				$widget = json_decode($info, true);
				
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
				
				$widgets[] = $widget;
			}
		}
		$d->close();
		
		
		
		// We have the list with the widgets, paths and names
		//print_r($widgets);
		
		require_once '../php/lib/DB.php';
		
		$db = new DB();
		$db->set_user_id(GLOBAL_USER_ID);
		$db->enable_debug_mode(true);
		
		foreach ($widgets as &$widget) {
			$db->create_widget($widget['name']);
			$id = $db->LAST_MYSQL_ID;
			if ($id === null) {
				$id = $db->get_widget($widget['name']);
				$id = $id['ID'];
			}
			$version = 1;
			var_dump($id);
			$db->create_widget_version($id);
			foreach ($widget['files'] as &$file) {
				$file_contents = file_get_contents($file['path']);
				$db->upload_widget_version_file($id, $version, $file['name'], file_mimetype($file['name']), $file_contents);
			}
			$db->set_widget_tags($id, $widget['tags']);
			//$db->set_widget_creation_date($id, $widget['date']);
			//$db->set_widget_autor();
			$db->publicate_widget_version($id, $version);
			$db->set_widget_version_visibility($id, $version, $widget['visible']);
		}
		
	break;
}

function filter_directory(&$directory_resource, $show_folders = true, $show_files = true) {
	if (false !== $entry = $directory_resource->read()) {
		if ($entry === '.' || $entry === '..') {
			return filter_directory($directory_resource, $show_folders, $show_files);
		} else {
			if ($show_folders && is_dir($directory_resource->path . $entry) ||
					$show_files && is_file($directory_resource->path . $entry)) {
				return $entry;
			}
		}
	} else {
		return false;
	}
}

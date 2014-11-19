Select steep:<p>

<a href="?steep=install-db">Install database</a><br>
<a href="?steep=update-db">Update database</a><br>

<a href="?steep=install-widgets">Install default widgets</a><br>
<a href="?steep=update-widgets">Update default widgets</a><br>

<pre>
<?php

$steep = $_GET['steep'];

switch ($steep) {
	case 'install-widgets':
		// search inside the widgets folder and add all the widgets to the user with id -1.
		// Admin user must be able to edit this afterwards
		
		$widgets_path = '../../widgets/';
		
		$widgets = array();
		
		$d = dir($widgets_path);
		while (false !== ($entry = filter_directory($d, true, false))) {
			if ($entry === '.' || $entry === '..') continue;
			
			if (is_dir($widgets_path.$entry)) {
				// the widget name will be the folder name
				$widget = array('name' => $entry, 'files' => array());
				
				$widget_path = $widgets_path . $entry . '/';
				
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
		$db->set_user_id(-1);
		
		foreach ($widgets as &$widget) {
			$res = $db->create_widget($widget['name']);
			var_dump($res);
			$id = $db->LAST_MYSQL_ID;
			$version = 1;
			var_dump($id);
			print_r($widget);
			$db->create_widget_version($id);
			foreach ($widget['files'] as &$file) {
				$file_contents = file_get_contents($file['path']);
				$db->upload_widget_version_file($id, $version, $file['name'], file_mimetype($file['name']), $file_contents);
			}
			$db->publicate_widget_version($id, $version);
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

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


header('Content-Type: text/html; charset=UTF-8');


session_start();
if(!isset($_SESSION['user'])){
	exit;
}


require_once 'php/config.php';
require_once 'php/class/DB.php';
require_once 'php/functions/generic.php';

$db = new DB();

?>

<!doctype html>
<html>
<head>
	<title>Homepage</title>
	<link rel="stylesheet" href="css/reset.min.css"/>
</head>
<body>

<!--
Mirar qué widgets tiene el usuario

Recorrerlos todos e incluir el js que les corresponde.

Un widget debe de contener lo siguiente: 
- Javascript que lo compone
- Posición y tamaño donde está situado

El javascript dibujará todo el widget, incluido el div que lo contendrá en el body
El javascript debe de estar contenido en su totalidad en una función anónima
El javascript tendrá acceso a la API para leer y escribir variables de cualquier widget. Además la api le suministrará la url a los archivos que le pida.
El javascript tendrá acceso a la posición y tamaño indicado y podrá editarlo ya que serán variables accesibles desde la API
-->

<script>
	
(function(){

	// Variables for the config widget
	var CONFIG = [];
	
	<?php echo file_get_contents('php/api.js/api.js');?>


	<?php

	// Widgets del usuario
	$widgets_usuario = $db->get_widgets_user();
	foreach($widgets_usuario as &$widget){
		// Pick the correct widget version
		$version = $db->get_using_widget_version_user($widget);
		
		// Create the html that will call the script
		//echo "<script src=\"widgetfile.php?widgetID={$widget['ID']}&widgetVersion={$version}&name=main.js\"></script>";
		$data = $db->get_widget_version_file($widget['ID'], $version, 'main.js');
		$data = &$data[0];
		$data = &$data['data'];
		?>
		
		(function(API_F){
			var API = (function(API_F, widgetID){
				return {
					"Storage": {
						"localStorage": {
							/*"set"(key, value, callback) -> Storage.localStorage
							"get"(key, callback) -> value
							"delete"(key, callback) -> Storage.localStorage
							"deleteAll"(callback) -> Storage.localStorage
							"exists"(key, callback) -> bool*/
						},
						"remoteStorage": {
							"set":function(key, value, callback){
								var command = {'action':1,'widget':widgetID,'variables':{key:value}};
								API_F.call(command, function(e){callback(e[key]);});
							},
							"get":function(key, callback){
								var command = {'action':0,'widget':widgetID,'variables':key};
								API_F.call(command, function(e){callback(e[key]);});
							}/*,
							"delete"(key, callback) -> Storage.remoteStorage
							"deleteAll"(callback) -> Storage.remoteStorage
							"exists"(key, callback) -> bool*/
						},
						"sharedStorage": {
							"set":function(key, value, callback){
								var command = {'action':1,'widget':'global','variables':{key:value}};
								API_F.call(command, function(e){callback(e[key]);});
							},
							"get":function(key, callback){
								var command = {'action':0,'widget':'global','variables':key};
								API_F.call(command, function(e){callback(e[key]);});
							}
							/*"set"(key, value, callback) -> Storage.sharedStorage
							"get"(key, callback) -> Storage.sharedStorage
							"delete"(key, callback) -> Storage.sharedStorage
							"exists"(key, callback) -> bool*/
						}
					},
					
					
					
					
					
					"url": function(name){return API_F.url(widgetID, name);}
				}
			})(API_F, '<?php echo $widget['ID'];?>');
			API_F = null;
			
			<?php echo $data;?>
			
			if(typeof CONFIG_function !== 'undefined'){
				CONFIG.push({
					'name':'<?php echo strtr($widget['name'], array("'","\\'"));?>',
					'function':CONFIG_function
				});
			}
		})(API_F);
		
	<?php }	?>
})();
</script>

</body>
</html>
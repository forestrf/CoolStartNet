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


require_once 'php/functions/generic.php';
$db = open_db_session();
if(!isset($_SESSION['user'])){
	exit;
}

?>

<!doctype html>
<html>
<head>
	<title>Homepage</title>
	<link rel="stylesheet" href="css/reset.min.css"/>
	<script src="js/crel2.js"></script>
	<script src="js/api.js"></script>
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-54264686-1', 'auto');
		ga('send', 'pageview');
	</script>
</head>
<body>

<script id="delete_me">
	
(function(){
	// prevent innerHTML from reading the widgetID + secret to prevent widgets manipulate other widgets without consent
	var t = document.getElementById("delete_me");
	t.parentNode.removeChild(t);
	delete t;
	// Prevent eval
	eval = function(){};

	// Variables for the config widget
	var CONFIG = [];

	<?php

	// Widgets del usuario
	$widgets_usuario = $db->get_widgets_user();
	foreach($widgets_usuario as &$widget){
		// Pick the correct widget version
		$version = $db->get_using_widget_version_user($widget);
		
		// Create the html that will call the script
		//echo "<script src=\"widgetfile.php?widgetID={$widget['ID']}&widgetVersion={$version}&name=main.js\"></script>";
		$data = $db->get_widget_version_file($widget['ID'], $version, 'main.js');
		if(!$data){
			continue;
		}
		?>
		
		(function(API){
			API = API.init("<?php echo $widget['ID'];?>", "<?php echo hash_api($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_API);?>", "<?php echo WEB_PATH?>");
			
			<?php echo $data[0]['data'];?>
			
			if(typeof CONFIG_function !== 'undefined'){
				CONFIG.push({
					'name':'<?php echo strtr($widget['name'], array("'","\\'"));?>',
					'function':CONFIG_function
				});
			}
		})(API);
		
	<?php }	?>
})();
</script>

</body>
</html>
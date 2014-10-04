<?php

# This file is part of CoolStart.net.
#
#	 CoolStart.net is free software: you can redistribute it and/or modify
#	 it under the terms of the GNU Affero General Public License as published by
#	 the Free Software Foundation, either version 3 of the License, or
#	 (at your option) any later version.
#
#	 CoolStart.net is distributed in the hope that it will be useful,
#	 but WITHOUT ANY WARRANTY; without even the implied warranty of
#	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#	 GNU Affero General Public License for more details.
#
#	 You should have received a copy of the GNU Affero General Public License
#	 along with CoolStart.net.  If not, see <http://www.gnu.org/licenses/>.



// this functions returns the base html
function render_wrapper($title = 'Homepage - CoolStart.net', $content, $compress = false){
	require_once __DIR__.'/../config.php';
	
	ob_start();
?>

<!doctype html>
<html>
<head>
	<title><?=$title?></title>
	<link rel="stylesheet" href="//<?=WEB_PATH?>css/reset.min.css"/>
	<link rel="stylesheet" href="//<?=WEB_PATH?>css/renderer.css"/>
</head>
<body>
	<div class="wrapper">
		<div class="widgets" id="widgets0"></div>
		
		<div class="bottom_bar" id="bottom_bar"></div>
	</div>
	
	<script src="//<?=WEB_PATH?>js/crel2.js"></script>
	<script src="//<?=WEB_PATH?>js/api.js"></script>
	<script><?=ANALYTICS_JS?></script>

	<script id="delete_me">
		// generate the links of the bottom menu bar
		(function(){
			var C = crel2;
			
			var menu = document.getElementById("bottom_bar");
			
			C(menu
				,C("a", ["class", "btn",                     "href", "//<?=WEB_PATH?>"],                           "Home")
				,C("a", ["class", "btn",                     "href", "//<?=WEB_PATH?>manage-widgets"],             "Manage widgets")
				,C("a", ["class", "btn",                     "href", "//<?=WEB_PATH?>options"],                    "Options")
				,C("a", ["class", "btn",                     "href", "//<?=WEB_PATH?>developers"],                 "Developers")
				,C("a", ["class", "btn", "target", "_blank", "href", "http://<?=FORUM_WEB_PATH?>"],                "forum")
				,C("a", ["class", "btn",                     "href", "//<?=WEB_PATH?>help"],                       "help")
				,C("a", ["class", "btn",                     "href", "//<?=WEB_PATH?>about"],                      "about")
				,C("a", ["class", "btn", "target", "_blank", "href", "https://github.com/forestrf/CoolStartNet"],  "GitHub")
				,C("a", ["class", "btn",                     "href", "//<?=WEB_PATH?>user?action=logout"],         "Logout")
			);
		})();
	</script>
	
	<?=$content?>
	
	</body>
</html>

<?php
	$html = ob_get_contents();
	ob_end_clean();

	if ($compress) {
		require_once __DIR__.'/minify/min/lib/Minify/HTML.php';
		require_once __DIR__.'/minify/min/lib/Minify/CSS.php';
		require_once __DIR__.'/minify/min/lib/Minify/CSS/Compressor.php';
		//require_once __DIR__.'/minify/min/lib/JSMin.php';
		require_once __DIR__.'/minify/JSHrink/src/Minifier.php';

		$html = Minify_HTML::minify($html, array(
			'cssMinifier' => array('Minify_CSS', 'minify'),
			//'jsMinifier' => array('JSMin', 'minify')
			'jsMinifier' => array('JShrink\Minifier', 'minify')
		));
	}

	return $html;
}

// This function generates the full html page of the user page. It can be cached
function render(&$db, $compress = false){
	require_once __DIR__.'/../config.php';

	ob_start();
?>

<script id="delete_me">
	(function(){
		// prevent innerHTML from reading the widgetID + secret to prevent widgets manipulate other widgets without consent
		var t = document.getElementById("delete_me");
		t.parentNode.removeChild(t);
		delete t;
		
		// Make a copy of window.API to prevent modifications from widgets to the api used to construct other widgets
		var API = API_GENERATOR();

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
			if (!$data) {
				continue;
			}
			?>

			(function(API){
				API = API.init("<?=$widget['ID'];?>",
						"<?=hash_api($_SESSION['user']['RND'], $widget['ID'], PASSWORD_TOKEN_API)?>",
						<?=server_vars_js();?>);



				<?=$data[0]['data'];?>

				if(typeof CONFIG_function !== 'undefined'){
					CONFIG.push({
						'name':'<?=str_replace("'", "\\'", $widget['name']);?>',
						'function':CONFIG_function
					});
				}
			})(API);
			
		<?php }	?>
	})();
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();

	return render_wrapper('Homepage - CoolStart.net', $html, $compress);
}
?>

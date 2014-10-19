<?php
	require_once __DIR__.'/php/defaults.php';
	require_once __DIR__.'/php/config.php';
	require_once __DIR__.'/php/functions/generic.php';
	require_once __DIR__.'/php/lib/renderer.php';
	
	ob_start();
?>

<script src="//<?=WEB_PATH?>js/ipa.js"></script>
<script src="//<?=WEB_PATH?>js/generic.js"></script>
<link href="//<?=WEB_PATH?>css/widget-box.css" rel="stylesheet"/>
<link href="//<?=WEB_PATH?>css/developers.css" rel="stylesheet"/>

<script>
	(function(API, IPA){
		var C = crel2;
		var API = API.init();
		var IPA = IPA.init(<?=server_vars_js()?>);
		var div = document.getElementById('widgets0');
		
		/*
		Crear y administrar widgets
		
		js api
		
		GitHub
		*/
		
		var own_widgets_list, developers_menu, developers_back;
		
		C(div,
			C("div", ["class", "left panel"],
				C("div", ["class", "create"],
					C("input", ["class", "name", "placeholder", "Widget name"]),
					C("input", ["class", "button", "type","button", "value", "Create widget"])
				),
				C("div", ["class", "manage"],
					"Manage your own widgets",
					own_widgets_list = C("div", ["class", "widget_list"])
				)
			),
			C("div", ["class", "right panel"],
				C("div", ["class", "developers_menu"],
					C("div", "Developer's FAQ"),
					developers_menu = C("ul",
						C("li", C("a", ["href", "doc/reader.html", "onclick", show_docs], "Documentation")),
						C("li", C("a", ["href", "//..."], "How to start")),
						C("li", C("a", ["href", "//..."], "Limitations (What you can't do)")),
						C("li", C("a", ["href", "//..."], "Benefits (What are you able to do)")),
						C("li", C("a", ["href", "//..."], "Good practices"))
					),
					developers_back = C("a", ["style", "display:none", "onclick", devs_back], "Back")
				),
				developers_space = C("div", ["class", "developers_space"])
			)
		);
		
		function show_docs() {
			developers_space.appendChild(C("iframe", ["src", "doc/reader.html", "class", "doc_iframe"]));
			developers_menu.style.display = "none";
			developers_back.style.display = "";
			return false;
		}
		
		function devs_back() {
			developers_menu.style.display = "";
			developers_space.style.display = "";
			developers_space.innerHTML = "";
			developers_back.style.display = "none";
		}
		
		
		
		
		
		
		API.xhr(
			'widgets?action=user-created-list',
			'',
			function (data) {
				data = JSON.parse(data);
				if (data.status = "OK") {
					for (var i = 0, l = data.response.length; i < l; i++) {
						own_widgets_list.appendChild(generate_widget_element(data.response[i]));
					}
				}
			}
		);
		
		
		
		
		
		
	})(API, IPA);
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo render_wrapper('Manage widgets - CoolStart.net', $html, false);
?>

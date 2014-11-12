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
		
		var widgets_menu, own_widgets_list, developers_menu, developers_back, widgets_back;
		
		C(div,
			C("div", ["class", "left panel"],
				widgets_menu = C("div",
					C("div", ["class", "create"],
						C("input", ["class", "name", "placeholder", "Widget name"]),
						C("input", ["class", "button", "type","button", "value", "Create widget"])
					),
					C("div", ["class", "manage"],
						"Manage your own widgets",
						own_widgets_list = C("div", ["class", "widget_list"])
					)
				),
				C("div", ["class", "links"],
					widgets_back = C("a", ["style", "display:none", "onclick", widgs_back], "Back")
				),
				widgets_space = C("div", ["class", "widgets_space"])
			),
			C("div", ["class", "right panel"],
				C("div", ["class", "developers_menu"],
					C("div", "Developer's FAQ"),
					C("div", ["class", "links"],
						developers_menu = C("ul",
							C("li", C("a", ["href", "doc/reader.html", "onclick", show_docs], "Documentation")),
							C("li", C("a", ["href", "//..."], "How to start")),
							C("li", C("a", ["href", "//..."], "Limitations (What you can't do)")),
							C("li", C("a", ["href", "//..."], "Benefits (What are you able to do)")),
							C("li", C("a", ["href", "//..."], "Good practices"))
						),
						developers_back = C("a", ["style", "display:none", "onclick", devs_back], "Back")
					)
				),
				developers_space = C("div", ["class", "developers_space"])
			)
		);
		
		function show_docs(path) {
			if (path === undefined || typeof path !== "string") {
				path = "";
			}
			devs_back();
			devs_fwd();
			
			developers_space.appendChild(C("iframe", ["src", "doc/reader.html#" + path, "class", "doc_iframe"]));
			return false;
		}
		
		function devs_back() {
			developers_menu.style.display = "";
			developers_space.style.display = "";
			developers_space.innerHTML = "";
			developers_back.style.display = "none";
		}
		
		function devs_fwd() {
			developers_menu.style.display = "none";
			developers_back.style.display = "";
		}
		
		
		
		
		
		
		function widgs_back() {
			widgets_menu.style.display = "";
			widgets_space.style.display = "";
			widgets_space.innerHTML = "";
			widgets_back.style.display = "none";
		}
		
		function widgs_fwd() {
			widgets_menu.style.display = "none";
			widgets_back.style.display = "";
		}
		
		
		
		
		function manage_widget(data){
			widgs_back();
			widgs_fwd();
			
			C(widgets_space,
				C("input", ["type", "text", "value", data.name, "name", "name"]), "Name", C("br"),
				C("input", ["type", "text", "value", data.description, "name", "description"]), "Description", C("br"),
				C("input", ["type", "file", "value", data.image, "name", "image"]), "Image", C("br"),
				C("input", ["type", "text", "value", data.tags, "name", "tags"]), "Tags (Comma-separated)", C("br"),
				"Versions", C("br")
			);
			
			console.log(data);
			
			widgets_space.appendChild(C("div"));
		}
		
		
		
		
		
		
		
		API.xhr(
			'widgets?action=user-created-list',
			'',
			function (data) {
				data = JSON.parse(data);
				if (data.status === "OK") {
					for (var i = 0, l = data.response.length; i < l; i++) {
						own_widgets_list.appendChild(generate_widget(data.response[i]));
					}
				}
			}
		);
		
		function generate_widget(data) {
			var widget = generate_widget_element(data);
			
			widget.appendChild(
				C("div", ["class", "settings", "onclick", function(){
					manage_widget(data)
				}], "M A N A G E")
			);
			
			return widget;
		}
		
		
		
		
		
		
	})(API, IPA);
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo render_wrapper('Developers panel - CoolStart.net', $html, false);
?>

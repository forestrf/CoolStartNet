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
		
		var widgets_menu, own_widgets_list, developers_menu, developers_back, widgets_back,
		create_button, create_name;
		
		C(div,
			C("div", ["class", "left panel"],
				widgets_menu = C("div",
					C("div", ["class", "create"],
						create_name = C("input", ["class", "name", "placeholder", "Widget name"]),
						create_button = C("input", ["class", "button", "type","button", "value", "Create widget"])
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
		
		create_button.onclick = function() {
			API.xhr(
				'widgets?action=user-created-create',
				'name=' + create_name.value,
				function (data) {
					if (data.status === 'OK') {
						refresh_widgets_list();
					}
				}
			);
		}
		
		
		
		
		
		
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
				C("form", ["onsubmit", update],
					C("input", ["type", "hidden", "name", "IDwidget", "value", data.IDwidget]),
					C("table",
						C("tr",
							C("td", "Name"),
							C("td", C("input", ["type", "text", "value", data.name, "name", "name"]))
						),
						C("tr",
							C("td", "Description"),
							C("td", C("input", ["type", "text", "value", data.description, "name", "description"]))
						),
						C("tr",
							C("td", "Full Description"),
							C("td", C("textarea", ["name", "fulldescription"], data.fulldescription))
						),
						C("tr",
							C("td", "Image"),
							C("td", C("img", ["src", IPA.widgetImage(data.IDwidget, data.preview)]), C("input", ["type", "file", "value", data.image, "name", "image"]))
						),
						C("tr",
							C("td", C("input", ["type", "reset", "value", "Reset"])),
							C("td", C("input", ["type", "submit", "value", "Update"]))
						)
					)
				),
				"Versions", C("br")
			);
			
			console.log(data);
			
			widgets_space.appendChild(C("div"));
			
			function update(event) {
				event.preventDefault();
				console.log(event);
				//debugger;
				var formData = new FormData(event.originalTarget);
				API.xhr(
					'widgets/user-created-update',
					formData,
					function (data) {
						if (data.status === 'OK') {
							alert("Widget updated");
						} else {
							alert("There was a problem updating the widget");
						}
					},
					function () {
						alert("There was a problem updating the widget");
					}
				);
			}
		}
		
		
		
		
		
		
		function refresh_widgets_list() {
			API.xhr(
				'widgets?action=user-created-list',
				'',
				function (data) {
					if (data.status === "OK") {
						for (var i = 0, l = data.response.length; i < l; i++) {
							own_widgets_list.appendChild(generate_widget(data.response[i]));
						}
					}
				}
			);
		}
		refresh_widgets_list();
		
		function generate_widget(data) {
			var widget = generate_widget_element(data, IPA);
			widget.buttonuse.remove();
			widget.onclick = function(){
				manage_widget(data)
			};
			
			return widget;
		}
		
		
		
		
		
		
	})(API, IPA);
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo render_wrapper('Developers panel - CoolStart.net', $html, false);
?>

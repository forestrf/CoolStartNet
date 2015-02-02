<?php
	require_once __DIR__.'/php/defaults.php';
	require_once __DIR__.'/php/config.php';
	require_once __DIR__.'/php/functions/generic.php';
	require_once __DIR__.'/php/lib/renderer.php';
	
	$db = open_db_session();
	
	ob_start();
?>

<script src="//<?=WEB_PATH?>js/ipa.js"></script>
<script src="//<?=WEB_PATH?>js/generic.js"></script>
<link href="//<?=WEB_PATH?>css/widget-box.css" rel="stylesheet"/>
<link href="//<?=WEB_PATH?>css/developers.css" rel="stylesheet"/>
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet"/>


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
		
		var widgets_menu, own_widgets_list, developers_menu, developers_back, left_icon_list,
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
		}
		
		function widgs_fwd() {
			widgets_menu.style.display = "none";
		}
		
		
		
		
		function manage_widget(data){
			widgs_back();
			widgs_fwd();
			
			C(widgets_space,
				left_icon_list = C("div", ["class", "left_bar"],
					C("div", ["class", "fa fa-reply", "onclick", widgs_back])
				),
				C("div",
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
								C("td", "Images"),
								C("td", "prev images")
							),
							C("tr",
								C("td", C("input", ["type", "reset", "value", "Reset"])),
								C("td", C("input", ["type", "submit", "value", "Update"]))
							)
						)
					)
				)
			);
			
			API.xhr(
				'widgets/user-created-files-list',
				'widgetID=' + data.IDwidget,
				function (data) {
					if (data.status === 'OK') {
						for (var i = 0; i < data.response.length; i++) {
							C(left_icon_list,
								C("div", ["class", "fa " + icon_from_filename(data.response[i].name), "onclick", inspect_widget_file(data.IDwidget, data.response[i].name)], C('span', data.response[i].name))
							)
						}
					}
				}
			);
			//left_icon_list
			
			console.log(data);
			
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
		
		function inspect_widget_file(IDwidget, filename) {
			
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
		
		function icon_from_filename(filename) {
			if (filename.indexOf('.') !== -1) {
				var extension = filename.substr(filename.lastIndexOf('.') + 1);
				
				for (var icon in icon_from_extension) {
					console.log(icon);
					if (icon_from_extension[icon].indexOf(extension) !== -1) {
						return icon;
					}
				}
			}
			return 'fa-file-o';
		}
		
		/*
		file-archive-o
		file-excel-o
		file-pdf-o
		file-powerpoint-o
		file-word-o
		*/
		
		var icon_from_extension = {
			'fa-file-image-o': ['ico', 'jpe', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tif', 'tiff', 'svg'],
			'fa-file-code-o':  ['js', 'css', 'htm', 'html'],
			'fa-file-text-o':  ['txt'],
			'fa-file-video-o': ['asf', 'asr', 'asx', 'avi', 'asf', 'webm', 'mp2', 'mpe', 'mpeg', 'mpg', 'mpv2', 'm1v', 'm2v', 'mov'],
			'fa-file-audio-o': ['wav', 'mp3', 'm2a', 'mp2', 'mpa', 'm3u', 'mid', 'midi']
		};
		
		
		
		
		
		
	})(API, IPA);
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo render_wrapper('Developers panel - CoolStart.net', $html, false);
?>

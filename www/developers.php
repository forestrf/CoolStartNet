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
		
		var menu_left, menu_right, content_left, content_right;
		
		C(div,
			menu_left = C("div", ["class", "menu_scrollable left"]/*,
				C("div", ["class", "menu_elem"], "Back"),
				C("div", ["class", "menu_elem"], "2"),
				C("div", ["class", "menu_elem"], "3"),
				C("div", ["class", "menu_elem"], "4"),
				C("div", ["class", "menu_elem"], "5"),
				C("div", ["class", "menu_elem"], "6"),
				C("div", ["class", "menu_elem"], "7"),
				C("div", ["class", "menu_elem"], "8"),
				C("div", ["class", "menu_elem"], "9"),
				C("div", ["class", "menu_elem"], "10"),
				C("div", ["class", "menu_elem"], "11"),
				C("div", ["class", "menu_elem"], "12"),
				C("div", ["class", "menu_elem"], "13"),
				C("div", ["class", "menu_elem"], "14"),
				C("div", ["class", "menu_elem"], "15"),
				C("div", ["class", "menu_elem"], "16"),
				C("div", ["class", "menu_elem"], "17")*/
			),
			content_left = C("div", ["class", "body_content left"]),
			content_right = C("div", ["class", "body_content right"]),
			menu_right = C("div", ["class", "menu_scrollable right"])
		);
		
		API.document.wrapElement(content_left);
		API.document.wrapElement(content_right);
		
		menu_left.style = "display:none";
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		function draw_widget_list() {
			var create_button, create_name;
			
			content_left.innerHTML = '';
			content_left.removeClass("with_bar");
			menu_left.innerHTML = '';
			menu_left.style.display = 'none';
			
			C(content_left,
				C("div", ["class", "create"],
					create_name = C("input", ["class", "name", "placeholder", "Widget name"]),
					create_button = C("input", ["class", "button", "type","button", "value", "Create widget"])
				)
			);
			
			create_button.onclick = function() {
				API.xhr(
					'widgets?action=user-created-create',
					'name=' + create_name.value,
					function (data) {
						if (data.status === 'OK') {
							draw_widget_list();
						}
					}
				);
			}		
		
			API.xhr(
				'widgets?action=user-created-list',
				'',
				function (data) {
					if (data.status === "OK") {
						for (var i = 0, l = data.response.length; i < l; i++) {
							content_left.appendChild(generate_widget(data.response[i]));
						}
					}
				}
			);
		}
		draw_widget_list();
		
		
		
		
		
		/*
		Crear y administrar widgets
		
		js api
		
		GitHub
		*/
		
		// C("li", C("a", ["href", "doc/reader.html", "onclick", show_docs], "Documentation")),
		
		
		
		
		
		
		function show_docs(path) {
			if (path === undefined || typeof path !== "string") {
				path = "";
			}
			
			developers_space.appendChild(C("iframe", ["src", "doc/reader.html#" + path, "class", "doc_iframe"]));
			return false;
		}
		
		
		
		
		function manage_widget(data){
			
			content_left.innerHTML = '';
			content_left.addClass("with_bar");
			menu_left.innerHTML = '';
			
			menu_left.style.display = '';
			C(menu_left,
				C("div", ["class", "menu_elem", "onclick", draw_widget_list], "Back"),
				C("div", ["class", "menu_elem", "onclick", function(data){manage_widget(data)}], "Manage info"),
				C("div", ["class", "menu_elem", "onclick", ''], "Upload file"),
				C("div", ["class", "menu_elem_title"], "widget files")
			);
			
			
			
			
			
			C(content_left,
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
							C(menu_left,
								C("div", ["class", "menu_elem", "onclick", inspect_widget_file(data.IDwidget, data.response[i].name)],
									C("div", ["class", "file_icon fa " + icon_from_filename(data.response[i].name)]),
									C('span', data.response[i].name)
								)
							)
						}
					}
				}
			);
			
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

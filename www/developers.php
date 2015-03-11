<?php
	require_once __DIR__.'/php/defaults.php';
	require_once __DIR__.'/php/config.php';
	require_once __DIR__.'/php/functions/generic.php';
	require_once __DIR__.'/php/lib/renderer.php';
	
	$db = open_db_session();
	
	ob_start();
?>

<script src="//<?=WEB_PATH?>js/generic.js"></script>
<link href="//<?=WEB_PATH?>css/widget-box.css" rel="stylesheet"/>
<link href="//<?=WEB_PATH?>css/developers.css" rel="stylesheet"/>
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet"/>


<script>
	(function(API){
		var C = crel2;
		var API_Original = API;
		var API = API.init(0,'',<?=server_vars_js()?>);
		var div = document.getElementById('widgets0');
		
		var menu_left, menu_right, content_left, content_right;
		
		C(div,
			menu_left = C("div", ["class", "menu_scrollable left"]),
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
			
			API = API_Original.init(data.IDwidget,'',<?=server_vars_js()?>);
			
			content_left.innerHTML = '';
			content_left.addClass("with_bar");
			menu_left.innerHTML = '';
			
			menu_left.style.display = '';
			C(menu_left,
				C("div", ["class", "menu_elem", "onclick", draw_widget_list], C("div", ["class", "file_icon fa fa-reply"]), C('span', 'Back')),
				C("div", ["class", "menu_elem", "onclick", function(){manage_widget(data)}], C("div", ["class", "file_icon fa fa-sliders"]), C('span', 'Manage info')),
				C("div", ["class", "menu_elem", "onclick", 'function add files'], C("div", ["class", "file_icon fa fa-plus-square"]), C('span', 'Add files'))
			);
			
			
			var prevImages;
			
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
								C("td", C("img", ["src", API.url(data.preview, data.IDwidget)]), C("input", ["type", "file", "value", data.image, "name", "image"]))
							),
							C("tr",
								C("td", "Images"),
								prevImages = C("td", ["class", "preview_images"])
							),
							C("tr",
								C("td", C("input", ["type", "reset", "value", "Reset"])),
								C("td", C("input", ["type", "submit", "value", "Update"]))
							)
						)
					)
				)
			);
			
			for (var i = 0; i < data.images.length; i++) {
				C(prevImages, C("img", ["src", API.url(data.images[i], data.IDwidget)]));
			}
			
			console.log(data.images);
			
			
			
			API.xhr(
				'widgets/user-created-files-list',
				'widgetID=' + data.IDwidget,
				function (data) {
					if (data.status === 'OK') {
						for (var i = 0; i < data.response.length; i++) {
							C(menu_left,
								C("div", ["class", "menu_elem", "onclick", (function(i){
										return function(){
											inspect_widget_file(data.IDwidget, data.response[i].name);
										}
									})(i)],
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
				var target = event.originalTarget !== undefined ? event.originalTarget : event.target;
				var formData = new FormData(target);
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
			content_left.innerHTML = '';
			
			switch(type_from_filename(filename)) {
				case 'image':
					C(content_left,
						C("div", ["class", "widgetelement image"],
							C("img", ["src", API.url(filename)]),
							C("div", "update, delete, rename")
						)
					);
				break;
				case 'code':
					var textarea;
					C(content_left,
						textarea = C("textarea", ["class", "widgetelement code"],
							C("div", "update, delete, rename")
						)
					);
					API.xhr(
						API.url(filename),
						null,
						function (data) {
							textarea.value = data;
						},
						function () {
							alert("There was a problem loading the widget file");
						}
					);
				break;
				case 'text':
				
				break;
				case 'video':
				
				break;
				case 'audio':
				
				break;
				case 'unknown':
				
				break;
			}
		}
		
		
		
		
		
		function generate_widget(data) {
			var widget = generate_widget_element(data, API);
			
			var t = widget.buttonuse;
			t.parentNode.removeChild(t);
			
			widget.onclick = function(){
				manage_widget(data)
			};
			
			return widget;
		}
		
		function type_from_filename(filename) {
			var extension = filename.indexOf('.') !== -1 ? filename.substr(filename.lastIndexOf('.') + 1) : '';
			
			if (extension !== '') {
				for (var type in filename_type_extensions) {
					if (filename_type_extensions[type].indexOf(extension) !== -1) {
						return type;
					}
				}
			}
			
			return 'unknown';
		}
		
		function icon_from_filename(filename) {
			return icon_filename_type[type_from_filename(filename)];
		}
		
		var icon_filename_type = {
			'image':   'fa-file-image-o',
			'code':    'fa-file-code-o',
			'text':    'fa-file-text-o',
			'video':   'fa-file-video-o',
			'audio':   'fa-file-audio-o',
			'pdf':     'file-pdf-o',
			'unknown': 'fa-file-o'
		}
		
		var filename_type_extensions = {
			'image': ['ico', 'jpe', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tif', 'tiff', 'svg'],
			'code':  ['js', 'css', 'htm', 'html'],
			'text':  ['txt'],
			'video': ['asf', 'asr', 'asx', 'avi', 'asf', 'webm', 'mp2', 'mpe', 'mpeg', 'mpg', 'mpv2', 'm1v', 'm2v', 'mov'],
			'audio': ['wav', 'mp3', 'm2a', 'mp2', 'mpa', 'm3u', 'mid', 'midi'],
			'pdf':   ['pdf']
		};
		
		
		
		
		
		
	})(API);
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo render_wrapper('Developers panel - CoolStart.net', $html, false);
?>

<?php
	require_once __DIR__.'/php/defaults.php';
	require_once __DIR__.'/php/config.php';
	require_once __DIR__.'/php/functions/generic.php';
	require_once __DIR__.'/php/lib/renderer.php';
	
	$db = open_db_session();
	
	ob_start();
?>

<script src="//<?=WEB_PATH?>js/generic.js"></script>
<link rel="stylesheet" href="//<?=WEB_PATH?>css/widget-box.css">
<link rel="stylesheet" href="//<?=WEB_PATH?>css/developers.css">
<link rel="stylesheet"  href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

<!--START Codemirror-->
<link rel="stylesheet" href="//<?=WEB_PATH?>js/codemirror/lib/codemirror.css">
<script src="//<?=WEB_PATH?>js/codemirror/lib/codemirror.js"></script>
<link rel="stylesheet" href="//<?=WEB_PATH?>js/codemirror/addon/dialog/dialog.css">
<link rel="stylesheet" href="//<?=WEB_PATH?>js/codemirror/addon/hint/show-hint.css">
<link rel="stylesheet" href="//<?=WEB_PATH?>js/codemirror/addon/tern/tern.css">
<link rel="stylesheet" href="//<?=WEB_PATH?>js/codemirror/theme/monokai.css">
<script src="//<?=WEB_PATH?>js/codemirror/mode/javascript/javascript.js"></script>
<script src="//<?=WEB_PATH?>js/codemirror/addon/dialog/dialog.js"></script>
<script src="//<?=WEB_PATH?>js/codemirror/addon/hint/show-hint.js"></script>
<script src="//<?=WEB_PATH?>js/codemirror/addon/tern/tern.js"></script>

<script src="//<?=WEB_PATH?>js/acorn/dist/acorn.js"></script>
<script src="//<?=WEB_PATH?>js/acorn/dist/acorn_loose.js"></script>
<script src="//<?=WEB_PATH?>js/acorn/dist/walk.js"></script>
<script src="//<?=WEB_PATH?>js/tern/doc/demo/polyfill.js"></script>
<script src="//<?=WEB_PATH?>js/tern/lib/signal.js"></script>
<script src="//<?=WEB_PATH?>js/tern/lib/tern.js"></script>
<script src="//<?=WEB_PATH?>js/tern/lib/def.js"></script>
<script src="//<?=WEB_PATH?>js/tern/lib/comment.js"></script>
<script src="//<?=WEB_PATH?>js/tern/lib/infer.js"></script>
<script src="//<?=WEB_PATH?>js/tern/plugin/doc_comment.js"></script>
<!--END Codemirror-->


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
			content_right = C("div", ["class", "body_content right with_bar"]),
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
					'widgets/user-created-create',
					'name=' + create_name.value,
					function (data) {
						if (data.status === 'OK') {
							draw_widget_list();
						}
					}
				);
			}		
		
			API.xhr(
				'widgets/user-created-list',
				'',
				function (data) {
					if (data.status === "OK") {
						for (var i = 0, l = data.response.length; i < l; i++) {
							content_left.appendChild(generate_widget(data.response[i]));
						}
					}
				},
				function() {
					alert ("There was a problem downloading the list of widgets");
				}
			);
		}
		draw_widget_list();
		
		
		
		
		
		/*
		Crear y administrar widgets
		
		js api
		
		GitHub
		*/
				
		C(menu_right,
			C("div", ["class", "menu_elem", "onclick", show_docs], C("div", ["class", "file_icon fa fa-question-circle"]), C('span', 'JS API'))
		);
		
		
		
		
		
		
		function show_docs(path) {
			if (path === undefined || typeof path !== "string") {
				path = "";
			}
			
			content_right.innerHTML = "";
			C(content_right,
				C("div",
					C("iframe", ["src", "doc/reader.html#" + path, "class", "doc_iframe"])
				)
			);
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
				C("div", ["class", "menu_elem", "onclick", function(){add_files(data.IDwidget)}], C("div", ["class", "file_icon fa fa-plus-square"]), C('span', 'Add files'))
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
				function (data2) {
					if (data2.status === 'OK') {
						for (var i = 0; i < data2.response.length; i++) {
							C(menu_left,
								C("div", ["class", "menu_elem", "onclick", (function(i){
										return function(){
											inspect_widget_file(data.IDwidget, data2.response[i].name);
										}
									})(i)],
									C("div", ["class", "file_icon fa " + icon_from_filename(data2.response[i].name)]),
									C('span', data2.response[i].name)
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
		
		
		// http://stackoverflow.com/questions/2198470/javascript-uploading-a-file-without-a-file
		// elements is an array of: isFile (boolean), name (string), filename (string), mimetype (string), data (variable to send / binary blob)
		function beginQuoteFileUnquoteUpload(url, IDwidget, elements, callbackOK, callbackFAIL) {
			elements = elements.concat({name: "IDwidget", data: IDwidget});
			
			console.log(elements);
			// Define a boundary, I stole this from IE but you can use any string AFAIK
			var boundary = "---------------------------36861392015894";
			var x = new XMLHttpRequest();
			body = "";
			
			for (var i = 0; i < elements.length; i++) {
				body += '--' + boundary + '\r\n';
				if (elements[i].isFile !== undefined && elements[i].isFile === true) {
					body += 'Content-Disposition: form-data; name="files[]"; filename="' + encodeURIComponent(elements[i].filename) + '"\r\n'
					      + 'Content-type: '+elements[i].mimetype
				} else {
					body += 'Content-Disposition: form-data; name="' + elements[i].name + '"';
				}
				body += '\r\n\r\n' + elements[i].data + '\r\n';
			}
			
			body += '--' + boundary + '--';
		
			x.open("POST", url, true);
			x.setRequestHeader(
				"Content-type", "multipart/form-data; boundary="+boundary
			);
			x.onreadystatechange = 	x.ontimeout = function () {
				if (x.readyState == 4) {
					if (	x.status == 200) {
						// Don`t fail if it is not a json
						try {
							var response = JSON.parse(x.responseText);
						} catch(e) {
							callbackFAIL();
							return;
						}
						callbackOK(response);
					} else {
						callbackFAIL();
					}
				}
			}
			x.send(body);
		}
		
		function inspect_widget_file(IDwidget, filename) {
			content_left.innerHTML = '';
			var extension = filename.indexOf('.') !== -1 ? filename.substr(filename.lastIndexOf('.') + 1) : '';
			
			switch(type_from_filename(filename)) {
				case 'image':
					C(content_left,
						C("div", ["class", "widgetelement image"],
							C("img", ["src", API.url(filename)]),
							C("div", "Update, Delete, Rename")
						)
					);
				break;
				case 'code':
					var textarea;
					var editor;
					C(content_left,
						C("div", ["class", "widgetelement code"],
							C("div", ["class", "options"], 
								C("button", ["onclick", function() {
									beginQuoteFileUnquoteUpload(
										'widgets/user-created-files-edit',
										IDwidget,
										[{
											isFile: true,
											filename: filename,
											mimetype: mymetype_from_filename(filename),
											data: editor.getValue()
										}],
										function (data) {
											if (data.status === 'OK') {
												alert("File updated");
											} else {
												alert("There was a problem saving the uploaded file(s)");
											}
										},
										function () {
											alert("There was a problem uploading the file(s)");
										}
									);
								}], "Update"),
								"Delete, Rename"
							),
							textarea = C("textarea")
						)
					);
					
					//extension === 'js' o 'css'
					
					API.xhr(
						API.url(filename) + '?' + Math.random(), // ignore cache
						null,
						function (data) {
							textarea.value = data;
							
							// set up codemirror
							editor = CodeMirror.fromTextArea(textarea, {
								lineNumbers: true,
								mode: "javascript",
								theme: "monokai",
								indentWithTabs: true
							});
							
							editor.setOption("extraKeys", {
								"Ctrl-Space": function(cm) { server.complete(cm); },
								"Ctrl-I": function(cm) { server.showType(cm); },
								"Ctrl-O": function(cm) { server.showDocs(cm); },
								"Alt-.": function(cm) { server.jumpToDef(cm); },
								"Alt-,": function(cm) { server.jumpBack(cm); },
								"Ctrl-Q": function(cm) { server.rename(cm); },
								"Ctrl-.": function(cm) { server.selectName(cm); }
							})
							editor.on("cursorActivity", function(cm) { server.updateArgHints(cm); });
							
							document.querySelector(".CodeMirror").style.height = (window.innerHeight - 20 - 20) + "px";
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
		
		function add_files(IDwidget) {
			content_left.innerHTML = '';
			C(content_left,
				C("div",
					C("form", ["onsubmit", upload_files],
						C("input", ["type", "hidden", "name", "IDwidget", "value", IDwidget]),
						C("input", ["type", "file", "name", "files[]", "multiple", "multiple"]),
						C("input", ["type", "submit", "value", "Upload files"])
					)
				)
			);
			
			function upload_files(event) {
				event.preventDefault();
				console.log(event);
				//debugger;
				var target = event.originalTarget !== undefined ? event.originalTarget : event.target;
				var formData = new FormData(target);
				API.xhr(
					'widgets/user-created-files-add',
					formData,
					function (data) {
						if (data.status === 'OK') {
							alert("File(s) uploaded");
						} else {
							alert("There was a problem saving the uploaded file(s)");
						}
					},
					function () {
						alert("There was a problem uploading the file(s)");
					}
				);
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
					if (filename_type_extensions[type].indexOf(extension.toLowerCase()) !== -1) {
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
		
		
		
		var ecma5json = "";
		var server;
		API.xhr('//<?=WEB_PATH?>js/tern/defs/ecma5.json', null, function (data) {
			ecma5json = data;
			
			server = new CodeMirror.TernServer({defs: [JSON.parse(ecma5json)]});
		});
		
		
		
		
		var filename_mimetype_extensions = <?=json_encode(G::$mimetype_extensions)?>;
		
		function mymetype_from_filename(filename) {
			var extension = filename.indexOf('.') !== -1 ? filename.substr(filename.lastIndexOf('.') + 1) : '';
			
			if (extension !== '') {
				for (var type in filename_mimetype_extensions) {
					if (filename_mimetype_extensions[type].indexOf(extension.toLowerCase()) !== -1) {
						return type;
					}
				}
			}
			
			return 'text/plain';
		}
		
		ALGO = mymetype_from_filename;
		
	})(API);
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo render_wrapper('Developers panel - CoolStart.net', $html, false);
?>

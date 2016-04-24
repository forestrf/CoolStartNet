(function (root, factory){
	root.API_GENERATOR = factory;
	root.API = factory();
})(this, (function () {
	var max_wait = 100, //ms
		timeouts = [],
		callbacks = [],
		execute_modes = [],
		requests = [];
	
	var local_precall = function (mode, widgetID, key, value, callback) {
		if (callback === undefined) callback = function () {};
		
		
		var fullkey = widgetID + '-' + key;
		
		// ['get', 'set', 'del', 'delall', 'check']
		switch(mode) {
			case 0:
				callback(JSON.parse(localStorage.getItem(fullkey)));
			break;
			case 1:
				callback(localStorage.setItem(fullkey, JSON.stringify(value)) || true);
			break;
			case 2:
				localStorage.removeItem(fullkey);
				callback(true);
			break;
			case 3:
				var length = localStorage.length;
				for (var i = 0; i < length; i++) {
					var tkey = localStorage.key(i);
					if (tkey && tkey.indexOf(widgetID + '-') === 0) {
						localStorage.removeItem(tkey);
					}
				}
				callback(true);
			break;
			case 4:
				callback(localStorage.getItem(fullkey)?true:false);
			break;
		}
	};
	
	/*
	0 = get
	1 = set
	2 = delete
	3 = delete all
	4 = exists
	*/
	var precall = function (mode, widgetID, secret, key, value, callback) {
		if (callback === undefined) callback = function () {};
		
		if (requests[mode] === undefined) {
			requests[mode]  = {};
			callbacks[mode] = [];
			execute_modes[mode] = function () {execute(mode, callbacks[mode]);};
		}
		
		if (requests[mode][widgetID] === undefined) {
			requests[mode][widgetID] = {};
			requests[mode][widgetID]['hash'] = secret;
			requests[mode][widgetID]['keys'] = {};
		}
		
		requests[mode][widgetID]['keys'][key] = value;
		
		callbacks[mode].push({"callback":callback,"widgetID":widgetID,"key":key});
		clearTimeout(timeouts[mode]);
		timeouts[mode] = setTimeout(execute_modes[mode], max_wait);
	};
	
	// Execute and clean cache
	var execute = function (mode, cb) {
		var data = 'action=' + ['get', 'set', 'del', 'delall', 'check'][mode] + '&data=' + encodeURIComponent(JSON.stringify(requests[mode]));
		
		xhr('api', data, function (response) { //OK
			var i = 0;
			//console.log(xhr.responseText);
			
			// Go over the cb and generate a response
			if (response && response.response && response.response === 'OK') {
				i = 0;
				while (i < cb.length) {
					
					var widgetID = cb[i].widgetID;
					var key      = cb[i].key;
					
					// If received and key requested
					if (typeof response.content[widgetID] !== 'undefined' &&
							typeof response.content[widgetID][key] !== 'undefined') {
						cb[i++].callback(JSON.parse(response.content[widgetID][key]));
						continue;
					}
					cb[i++].callback(null);
				}
				return;
			}
		}, function () { //FAIL
			var i = 0;
			while (i < cb.length) {
				cb[i++].callback(null);
			}
		});
		
		requests[mode]  = {};
		callbacks[mode] = [];
	};
	
	// callback takes one argument
	// http://stackoverflow.com/questions/8567114/how-to-make-an-ajax-call-without-jquery
	// data = null or undefined para usar GET
	function xhr(url, data, callbackOK, callbackFAIL, responseIsJSON) {
		if (callbackFAIL === undefined) callbackFAIL = function(){};
		if (callbackOK === undefined) callbackOK = function(){};
		var isPost = data !== undefined && data !== null;
		var x;
		if (window.XMLHttpRequest) {
			// code for IE7+, Firefox, Chrome, Opera, Safari
			x = new XMLHttpRequest();
		} else {
			// code for IE6, IE5
			x = new ActiveXObject("Microsoft.XMLHTTP");
		}
		if (isPost) x.open('POST', url, true);
		else        x.open('GET', url, true);
		x.timeout = 30000;
		x.onreadystatechange = x.ontimeout = function () {
			if (x.readyState == 4) {
				if (x.status == 200) {
					// Don`t fail if it is not a json
					try {
						var response = isPost && (responseIsJSON === undefined || responseIsJSON) ? JSON.parse(x.responseText) : x.responseText;
					} catch(e) {
						callbackFAIL();
						return;
					}
					callbackOK(response);
				} else {
					callbackFAIL();
				}
			}
		};
		
		if (isPost) {
			if (typeof data === "string") {
				x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			} else {
				// http://stackoverflow.com/questions/2198470/javascript-uploading-a-file-without-a-file
				// data is an array of: isFile (boolean), name (string), filename (string), mimetype (string), data (variable to send / binary blob)
				var boundary = "---------------------------36861392015894";
				body = "";
				
				for (var i = 0; i < data.length; i++) {
					body += '--' + boundary + '\r\n';
					if (data[i].isFile !== undefined && data[i].isFile === true) {
						body += 'Content-Disposition: form-data; name="files[]"; filename="' + encodeURIComponent(data[i].filename) + '"\r\n'
						      + 'Content-type: ' + data[i].mimetype;
					} else {
						body += 'Content-Disposition: form-data; name="' + data[i].name + '"';
					}
					body += '\r\n\r\n' + data[i].data + '\r\n';
				}
				
				body += '--' + boundary + '--';
				
				data = body;
			
				x.setRequestHeader("Content-Type", "multipart/form-data; boundary=" + boundary);
			}
			
			x.send(data);
		} else x.send();
	}
	
	
	
	// Return an url to get a file of the widget
	var getUrl = function (widgetID, filename) {
		return 'widgetfile/' + widgetID + '/api/' + encodeURIComponent(filename);
	};
	
	
	
	function div_base(div) {
		function cRound(number, roundedTo) {
			return roundedTo === undefined || roundedTo === -1 ? number : (+number).toFixed(roundedTo);
		}

		div.hide = function () {
			div.style.display = 'none';
			return div;
		};
		div.unHide = function () {
			div.style.display = '';
			return div;
		};
			
		div.setPosition = function (left, top) {
			div.style.left = left + "%";
			div.style.top  = top + "%";
			return div;
		};
		
		div.getPosition = function (roundedTo) {
			return {
				"left": cRound(div.style.left.split("%")[0], roundedTo),
				"top":  cRound(div.style.top.split("%")[0],  roundedTo)
			};
		};
		
		div.setSize = function (width, height) {
			div.style.width  = width  + "%";
			div.style.height = height + "%";
			return div;
		};
		
		div.getSize = function (roundedTo) {
			return {
				"width":  cRound(div.style.width.split("%")[0],  roundedTo),
				"height": cRound(div.style.height.split("%")[0], roundedTo)
			};
		};
		
		div.setPositionSize = function (left, top, width, height) {
			if (typeof left === 'object') {
				top = left.top;
				width = left.width;
				height = left.height;
				left = left.left;
			}
			return div.setPosition(left, top).setSize(width, height);
		};
		
		div.getPositionSize = function (roundedTo) {
			var p = div.getPosition(roundedTo);
			var s = div.getSize(roundedTo);
			return {
				"left":   p.left,     
				"top":    p.top,
				"width":  s.width,
				"height": s.height
			};
		};
		
		div.addClass = function (className) {
			div.className += " "+className;
			return div;
		};
		div.removeClass = function (className) {
			div.className = (" " + div.className + " ").split(" " + className + " ").join("").trim();
			return div;
		};
		div.flipflopClass = function (className) {
			return div.className.indexOf(className) !== -1 ?
				div.removeClass(className) :
				div.addClass(className);
		};
		
		div.clear = function () {
			div.innerHTML = '';
		};
	}
	
	
	// There are 2 types of elements, bookmarks and folders.
	// Bookmarks contain the info of a individual bookmark.
	// folders make groups of bookmarks and folders.
	// folders have a placeholder in the bookmarks to allow order between bookmarks and folders.
	// custom placeholders in bookmarks can be placed simply using an invented type, like separators.
	// To allow custom placeholders, bookmarks type is checked by if it is or not a bookmark. If it is not, it is as a folder.
	// To work with placeholders you must use the moveBookmark and removeBookmark methods passing bool "true" to the "custom" parameter
	// everything can be amplified with more options, but no less (this is the minimum).
	// Widgets will read the options they can handle. This api doesn't delete unknown options.
	// Object structure example and code to generate it:
	/*
	{
		"folders":
		{
			"first folder":
			{
				"folders":{},
				"bookmarks":
				[
					{
						"type":"bookmark",
						"uri":"http://2"
					},
					{
						"type":"bookmark",
						"uri":"http://3"
					},
					{
						"type":"bookmark",
						"uri":"http://5"
					}
				]
			},
			"third folder":
			{
				"folders":
				{
					"second folder":
					{
						"folders":{},
						"bookmarks":[]
					}
				},
				"bookmarks":
				[
					{
						"type":"folder",
						"name":"second folder"
					},
					{
						"type":"bookmark",
						"uri":"http://4"
					},
					{
						"type":"bookmark",
						"uri":"http://6"
					}
				]
			}
		},
		"bookmarks":
		[
			{
				"type":"folder",
				"name":"third folder"
			},
			{
				"type":"bookmark",
				"uri":"http://1"
			},
			{
				"type":"folder",
				"name":"first folder"
			}
		]
	}
	
	// Generated using:
	API.bookmarks.createObject()
	.addBookmark("/", "http://1")
	.addFolder("/", "first folder")
	.addBookmark("/first folder", "http://2")
	.addBookmark("/first folder", "http://3")
	.addBookmark("/first folder", "http://4")
	.addBookmark("/first folder", "http://5")
	.addFolder("/", "second folder")
	.addFolder("/second folder/", "third folder")
	.addBookmark("/second folder/third folder/", "http://6")
	.moveBookmark("/first folder",2,"/second folder/third folder",0)
	.moveFolder("/second folder","third folder","/",0)
	.moveFolder("/",3,"/third folder",0);
	
	*/
	function bookmarks_base(bookmarks) {
		if (bookmarks === undefined) {
			bookmarks = {};
			bookmarks.folders   = {};
			bookmarks.bookmarks = [];
		}
		
		//Given a path with the pattern a/b/c returns the object from the folder c inside of the b inside of the a inside of the root
		function path_resolver(path) {
			path = path.split("/").reverse();
			var bookmark_path = bookmarks;
			while (path.length > 0) {
				var path_fragment = path.pop();
				if (path_fragment !== "") {
					bookmark_path = bookmark_path.folders[path_fragment];
				}
			}
			return bookmark_path;
		}
		
		return {
			"getObject": function () {return bookmarks;},
			"setObject": function (obj) {bookmarks = obj; return this;},
			"addBookmark": function (path, uri, title, favicon_uri) {
				var real_path = path_resolver(path);
				if (!real_path) {return this;}
				
				real_path.bookmarks.push({
					type: "bookmark",
					uri: uri,
					title: title,
					iconuri: favicon_uri
				});
				return this;
			},
			"addFolder": function (path, name) {
				if (name.indexOf("/") !== -1 || name.length === 0) {
					return this;
				}
				
				var real_path = path_resolver(path);
				if (!real_path) {return this;}
				
				// Prevent possible bugs
				if (real_path.folders instanceof Array) {
					real_path.folders = {};
				}
				real_path.folders[name] = {
					folders: {},
					bookmarks: []
				};
				
				real_path.bookmarks.push({
					type: "folder",
					name: name
				});
				return this;
			},
			// Returns references that, if modified, the original object breaks?
			"getBookmark": function (path, index) {
				var real_path = path_resolver(path);
				if (!real_path) {return false;}
				
				if (real_path.bookmarks[index] && real_path.bookmarks[index].type !== "folder") {
					return {
						uri: real_path.bookmarks[index].uri,
						title: real_path.bookmarks[index].title,
						iconuri: real_path.bookmarks[index].iconuri
					};
				}
				return false;
			},
			// Returns references that, if modified, the original object breaks?
			"getBookmarks": function (path) {
				var real_path = path_resolver(path);
				if (!real_path) {return false;}
				
				var result = [];
				for (var i = 0; i < real_path.bookmarks.length; i++) {
					if (real_path.bookmarks[i].type !== "folder") {
						result.push({
							index: i,
							uri: real_path.bookmarks[i].uri,
							title: real_path.bookmarks[i].title,
							iconuri: real_path.bookmarks[i].iconuri
						});
					}
				}
				return result;
			},
			// Returns references that, if modified, the original object breaks?
			"getFolders": function (path) {
				var real_path = path_resolver(path);
				if (!real_path) {return false;}
				
				var result = [];
				for (var i = 0; i < real_path.bookmarks.length; i++) {
					if (real_path.bookmarks[i].type === "folder") {
						result.push({
							index: i,
							folder: real_path.bookmarks[i].name
						});
					}
				}
				return result;
			},
			// Returns references that, if modified, the original object breaks?
			"getElements": function (path) {
				var real_path = path_resolver(path);
				if (!real_path) {return false;}
				
				var result = [];
				for (var i = 0; i < real_path.bookmarks.length; i++) {
					if (real_path.bookmarks[i].type === "folder") {
						result.push({
							type: 'folder',
							folder: real_path.bookmarks[i].name
						});
					}
					else if (real_path.bookmarks[i].type === "bookmark") {
						result.push({
							type: 'bookmark',
							uri: real_path.bookmarks[i].uri,
							title: real_path.bookmarks[i].title,
							iconuri: real_path.bookmarks[i].iconuri
						});
					} else {
						result.push({
							type: 'unknown',
							element: real_path.bookmarks[i]
						});
					}
				}
				return result;
			},
			"removeBookmark": function (path, index, force) {
				if (force === undefined) {force = false;}
				var real_path = path_resolver(path);
				if (!real_path) {return this;}
				
				if (force || real_path.bookmarks[index].type === "bookmark") {
					real_path.bookmarks.splice(index, 1);
				}
				return this;
			},
			"removeFolder": function (path, name_index) {
				var real_path = path_resolver(path);
				if (!real_path) {return this;}
				
				if (typeof name_index === "number") {
					if (real_path.bookmarks[name_index].type === "bookmark") {
						return this;
					}
					name_index = real_path.bookmarks.splice(name_index, 1)[0].name;
				} else {
					for (var i = 0; i < real_path.bookmarks.length; i++) {
						if (real_path.bookmarks[i].name === name_index) {
							if (real_path.bookmarks[i].type === "folder") {
								/*folder_bookmarks = real_path.bookmarks.splice(i, 1)[0];*/
								real_path.bookmarks.splice(i, 1);
							}
							break;
						}
					}
				}
				
				delete real_path.folders[name_index];
				return this;
			},
			// Moves a bookmark from one place to another. index starts from 0
			"moveBookmark": function (path_from, index_from, path_to, index_to, force) {
				if (force === undefined) {force = false;}
				var real_path_from = path_resolver(path_from);
				var real_path_to   = path_resolver(path_to);
				if (!real_path_from) {return this;}
				if (!real_path_to) {return this;}
				
				if (force || real_path_from.bookmarks[index_from].type === "bookmark") {
					var bookmark = real_path_from.bookmarks.splice(index_from ,1)[0];
					var temp = real_path_to.bookmarks.splice(index_to);
					real_path_to.bookmarks = real_path_to.bookmarks.concat(bookmark).concat(temp);
				}
				return this;
			},
			// Moves a bookmark from one place to another. index starts from 0
			"moveFolder": function (path_from, name_index_from, path_to, index_to) {
				var real_path_from = path_resolver(path_from);
				var real_path_to   = path_resolver(path_to);
				if (!real_path_from) {return this;}
				if (!real_path_to) {return this;}
				
				var folder_bookmarks;
				
				if (typeof name_index_from === "number") {
					if (real_path_from.bookmarks[name_index_from].type !== "folder") {
						return this;
					}
					folder_bookmarks = real_path_from.bookmarks.splice(name_index_from, 1)[0];
					name_index_from = folder_bookmarks.name;
				} else {
					for (var i = 0; i < real_path_from.bookmarks.length; i++) {
						if (real_path_from.bookmarks[i].name === name_index_from) {
							if (real_path_from.bookmarks[i].type !== "folder") {
								return this;
							}
							folder_bookmarks = real_path_from.bookmarks.splice(i, 1)[0];
							break;
						}
					}
				}
				// name_index_from and folder_bookmarks setted at this point
				
				// move folder from bookmarks list
				var temp = real_path_to.bookmarks.splice(index_to);
				real_path_to.bookmarks = real_path_to.bookmarks.concat(folder_bookmarks).concat(temp);
				
				//move folder from folders list
				var folder = real_path_from.folders[name_index_from];
				delete real_path_from.folders[name_index_from];
				real_path_to.folders[name_index_from] = folder;
				return this;
			},
			"editBookmark": function (path, index, uri, title, favicon_uri) {
				var real_path = path_resolver(path);
				if (!real_path) {return this;}
				
				real_path.bookmarks[index] = {
					type: "bookmark",
					uri: uri,
					title: title,
					iconuri: favicon_uri
				};
				return this;
			},
			"editFolder": function (path, old_name_index, new_name) {
				var real_path = path_resolver(path);
				var old_name = old_name_index;
				if (!real_path) {return this;}
				
				if (typeof old_name_index === "number") {
					if (real_path.bookmarks[old_name_index].type !== "folder") {
						return this;
					}
					old_name = real_path.bookmarks[old_name_index].name;
					real_path.bookmarks[old_name_index].name = new_name;
				} else {
					for (var i = 0; i < real_path.bookmarks.length; i++) {
						if (real_path.bookmarks[i].name === old_name) {
							if (real_path.bookmarks[i].type !== "folder") {
								return this;
							}
							real_path.bookmarks[i].name = new_name;
							break;
						}
					}					
				}
				
				
				real_path.folders[new_name] = real_path.folders[old_name];
				delete real_path.folders[old_name];
				
				return this;
			}
		};
	}
	
	
	
	
	
	function create_link_css(href) {
		var link = document.createElement("link");
		link.setAttribute("rel",  "stylesheet");
		link.setAttribute("type", "text/css");
		link.setAttribute("href", href);
		document.getElementsByTagName("head")[0].appendChild(link);
	}
	
	function create_css(css_text) {
		var newStyle = document.createElement('style');
		newStyle.appendChild(document.createTextNode(css_text));
		document.getElementsByTagName("head")[0].appendChild(newStyle);
	}
	
	function create_script_src(src, callback) {
		var s = document.createElement('script');
		s.setAttribute('src', src);
		if (callback !== undefined) {
			s.onload = callback;
		}
		document.body.appendChild(s);
	}
	
	
	
	var widgets = document.getElementById("widgets0");
	
	
	return {
		"init":function (widgetID, secret, server_vars) {
			if (undefined === widgetID) widgetID = -1;
			if (undefined === secret) secret = '';
			if (undefined === server_vars) server_vars = {};
			
			return {
				"storage": {
					"localStorage": {
						"get": function (key, callback) {
							local_precall(0, widgetID, key, null, callback);
							return this; //API.Storage.localStorage;
						},
						"set": function (key, value, callback) {
							local_precall(1, widgetID, key, value, callback);
							return this; //API.Storage.localStorage;
						},
						"delete": function (key, callback) {
							local_precall(2, widgetID, key, null, callback);
							return this; //API.Storage.localStorage;
						},
						"deleteAll": function (callback) {
							local_precall(3, widgetID, null, null, callback);
							return this; //API.Storage.remoteStorage;
						},
						"exists": function (key, callback) {
							local_precall(4, widgetID, key, null, callback);
							return this; //API.Storage.localStorage;
						}
					},
					"remoteStorage": {
						"get": function (key, callback) {
							precall(0, widgetID, secret, key, null, callback);
							return this; //API.Storage.remoteStorage;
						},
						"set": function (key, value, callback) {
							precall(1, widgetID, secret, key, value, callback);
							return this; //API.Storage.remoteStorage;
						},
						"delete": function (key, callback) {
							precall(2, widgetID, secret, key, null, callback);
							return this; //API.Storage.remoteStorage;
						},
						"deleteAll": function (callback) {
							precall(3, widgetID, secret, null, null, callback);
							return this; //API.Storage.remoteStorage;
						},
						"exists": function (key, callback) {
							precall(4, widgetID, secret, key, null, callback);
							return this; //API.Storage.remoteStorage;
						}
					},
					"sharedStorage": {
						"get": function (key, callback) {
							precall(0, -1, null, key, null, callback);
							return this; //API.Storage.sharedStorage;
						},
						"set": function (key, value, callback) {
							precall(1, -1, null, key, value, callback);
							return this; //API.Storage.sharedStorage;
						},
						"delete": function (key, callback) {
							precall(2, -1, null, key, null, callback);
							return this; //API.Storage.remoteStorage;
						},
						"exists": function (key, callback) {
							precall(4, -1, null, key, null, callback);
							return this; //API.Storage.remoteStorage;
						}
					}
				},
				"widget": {
					//"widgetsContainer": widgets,
					"create": function () {
						var div = document.createElement("div");
						div_base(div);
						div.addClass('widget_base');
						widgets.appendChild(div);
						return div;
					},
					"linkMyCSS": function (name) {
						create_link_css(getUrl(widgetID, name));
						return this; //API.widget
					},
					"linkExternalCSS": function (href) {
						create_link_css(href);
						return this; //API.widget
					},
					"InlineCSS": function (css_txt) {
						create_css(css_txt);
						return this; //API.widget
					},
					"linkMyJS": function (name, callback) {
						create_script_src(getUrl(widgetID, name), callback);
						return this; //API.widget
					},
					"linkExternalJS": function (href, callback) {
						create_script_src(href, callback);
						return this; //API.widget
					}
				},
				"document": {
					"createElement": function (tagName) {
						var elem = document.createElement(tagName);
						div_base(elem);
						return elem;
					},
					"wrapElement": function (element) {
						div_base(element);
						return this;
					},
					"widgets": widgets
				},
				"url": function (name, widgetIDManual) {
					return widgetIDManual === undefined ? getUrl(widgetID, name) : getUrl(widgetIDManual, name);
				},
				"xhr": xhr,
				"siteURLs": {
					'main'     : '//' + server_vars.WEB_PATH,
					'login'    : '//' + server_vars.WEB_PATH + 'user?action=login',
					'register' : '//' + server_vars.WEB_PATH + 'user?action=register',
					'forgot'   : '//' + server_vars.WEB_PATH + 'user?action=forgot',
					'logout'   : '//' + server_vars.WEB_PATH + 'user?action=logout'
				},
				"globals": {
					"captchaPublicKey": server_vars.CAPTCHA_PUB_KEY
				},
				"bookmarks": {
					"createObject": bookmarks_base,
					"getFavicon": function(url) {
						return '//' + server_vars.WEB_PATH + 'util/getfavicon?url=' + encodeURIComponent(url);
					}
				},
				"dropbox": {
					"getPathContents": function (path, callback) {
						xhr('/external', 'm=0&path='+encodeURIComponent(path), function (response) {
							
							// Go over the cb and generate a response
							if (response && response.folders && response.files) {
								callback(response);
							} else {
								callback(null);
							}
						}, function () {
							callback(null);
						});
						return this; //API.dropbox
					},
					"getFileURI": function (path) {
						return '//' + server_vars.WEB_PATH + 'externalfile/uid/' + server_vars.USER + '/file' + path;
					},
					"available": function (callback) {
						if (callback === undefined) callback = function () {};
						xhr('/external', 'm=1', function (response) {
							
							// Go over the cb and generate a response
							if (response && response.available) {
								callback(response.available);
							} else {
								callback(false);
							}
						}, function () {
							callback(false);
						});
						return this; //API.dropbox
					}
				}
			};
		}
	};
}));
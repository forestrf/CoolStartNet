/*
Consult pattern:

var consult = {
	'action'    : 'get',
	'widget'    : widgetID | -1,
	'variables' : 'variable1'
}

var consult = {
	'action'    : 'get',
	'widget'    : widgetID | -1,
	'variables' : ['variable1', ...]
}

var consult = {
	'action'    : 'set',
	'widget'    : widgetID | -1,
	'variables' : {'variable1':'value1', ...}
}
*/

// 0 = get
// 1 = set
// 2 = delete
var API_F = (function(){
	var max_wait = 100, //ms
		timeouts = [],
		callbacks = [],
		execute_modes = [],
		requests = [];
	
	function widget_add_secret(widgetID, secret){
		return widgetID + '-' + secret;
	}
	
	var precall = function(mode, widgetID, secret, key, value, callback){
		if(callback === undefined){
			callback = function(){};
		}
		if(secret){
			widgetID = widget_add_secret(widgetID, secret);
		}
		
		if(requests[mode] === undefined){
			requests[mode]  = {};
			callbacks[mode] = [];
			execute_modes[mode] = function(){execute(mode, callbacks[mode]);}
		}
		
		if(requests[mode][widgetID] === undefined){
			requests[mode][widgetID] = {};
		}
		
		requests[mode][widgetID][key] = value;
		
		callbacks[mode].push({"callback":callback,"widgetID":widgetID,"key":key});
		clearTimeout(timeouts[mode]);
		timeouts[mode] = setTimeout(execute_modes[mode], max_wait);
	}
	
	// Execute and clean cache
	var execute = function(mode, cb){
		var req = new XMLHttpRequest();
		req.open('POST', 'api.php', true);
		req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		req.onreadystatechange = function(aEvt){
			if(req.readyState == 4){
				if(req.status == 200){
					//console.log(req.responseText);
					var response = JSON.parse(req.responseText);
					
					// Go over the cb and generate a response
					if(response['response']==='OK'){
						var i = 0;
						while (i < cb.length) {
							
							var widgetID = cb[i]['widgetID'];
							var key      = cb[i]['key'];
							
							// If received and key requested
							if(typeof response['content'][widgetID] !== 'undefined' &&
							typeof response['content'][widgetID][key] !== 'undefined'){
								cb[i++]['callback'](JSON.parse(response['content'][widgetID][key]));
								continue;
							}
							cb[i++]['callback'](null);
						}
						return;
					}
				}
				var i = 0;
				while (i < cb.length) {
					cb[i++]['callback'](null);
				}
			}
		};
		var data = 'action=' + ['get', 'set', 'del'][mode] + '&data=' + encodeURIComponent(JSON.stringify(requests[mode]));
		req.send(data);
	
		requests[mode]  = {};
		callbacks[mode] = [];
	}
	
	
	
	
	// Return an url to get a file of the widget
	var getUrl = function(widgetID, filename){
		return 'widgetfile.php?widgetID=' + widgetID + '&api=1&name=' + escape(filename);
	}
	
	
	
	
	
	
	function Document(){
		return {
			"createElement": function(tagName){
				var elem = document.createElement(tagName);
				div_base(elem);
				return elem;
			}
		};
	}
	
	function div_base(div){
		function cRound(number, roundedTo){
			return roundedTo === undefined ? number : (+number).toFixed(roundedTo);
		}
	
		div["div"]  = div;
		div["hide"] = function(){
			div.style.display = 'none';
			return div;
		};
		div["unHide"] = function(){
			div.style.display = '';
			return div;
		};
			
		div["setPosition"] = function(left, top){
			div.style.left = left + "%";
			div.style.top  = top + "%";
			return div;
		};
		div.setPosition["left"] = function(left){div.style.left = left + "%"; return div;};
		div.setPosition["top"]  = function(top){ div.style.top  = top + "%";  return div;};
		
		div["getPosition"] = function(roundedTo){
			return {
				"left": cRound(div.style.left.split("%")[0], roundedTo),
				"top":  cRound(div.style.top.split("%")[0],  roundedTo)
			};
		};
		div.getPosition["left"] = function(roundedTo){return cRound(div.style.left.split("%")[0], roundedTo)};
		div.getPosition["top"]  = function(roundedTo){return cRound(div.style.top.split("%")[0],  roundedTo)};
		
		div["setSize"] = function(width, height){
			div.style.width  = width  + "%";
			div.style.height = height + "%";
			return div;
		};
		div.setSize["width"]  = function(width){ div.style.width  = width  + "%"; return div;};
		div.setSize["height"] = function(height){div.style.height = height + "%"; return div;};
		
		div["getSize"] = function(roundedTo){
			return {
				"width":  cRound(div.style.width.split("%")[0],  roundedTo),
				"height": cRound(div.style.height.split("%")[0], roundedTo)
			};
		};
		div.getSize["width"]  = function(roundedTo){return cRound(div.style.width.split("%")[0],  roundedTo)};
		div.getSize["height"] = function(roundedTo){return cRound(div.style.height.split("%")[0], roundedTo)};
		
		div["setPositionSize"] = function(left, top, width, height){
			return div.setPosition(left, top).setSize(width, height);
		};
		div.setPositionSize["left"]   = div.setPosition["left"];
		div.setPositionSize["top"]    = div.setPosition["top"];
		div.setPositionSize["width"]  = div.setSize["width"];
		div.setPositionSize["height"] = div.setSize["height"];
		
		div["getPositionSize"] = function(roundedTo){
			var p = div.getPosition(roundedTo);
			var s = div.getSize(roundedTo);
			return {
				"left":   p.left,     
				"top":    p.top,
				"width":  s.width,
				"height": s.height
			}
		};
		div.setPositionSize["left"]   = div.setPosition["left"];
		div.setPositionSize["top"]    = div.setPosition["top"];
		div.setPositionSize["width"]  = div.setSize["width"];
		div.setPositionSize["height"] = div.setSize["height"];
		
		div["addClass"] = function(className){
			div.className += " "+className;
			return div;
		};
		div["removeClass"] = function(className){
			div.className = div.className.split(className).join("").trim();
			return div;
		};
		div["setPriority"] = function(zIndex){
			div.style.zIndex = zIndex;
			return div;
		};
		div["getPriority"] = function(zIndex){
			return div.style.zIndex;
		}
	}
	
	
	
	function bookmarks_base(bookmarks){
		var folders_name   = "folders";
		var bookmarks_name = "bookmarks";
		
		var bookmark_uri_name = "uri";
		var bookmark_title_name = "title";
		var bookmark_iconuri_name = "iconuri";
		
		if(bookmarks === undefined){
			bookmarks = {};
			bookmarks[folders_name] = {};
			bookmarks[bookmarks_name] = [];
		}
		
		//Given a path with the pattern a/b/c returns the object from the folder c inside of the b inside of the a inside of the root
		function path_resolver(path){
			path = path.split("/").reverse();
			var bookmark_path = bookmarks;
			while(path.length > 0){
				var path_fragment = path.pop();
				bookmark_path = path_fragment === "" ? bookmark_path: bookmark_path[folders_name][path_fragment];
			}
			return bookmark_path;
		}
		
		function bookmark_swap(bookmarksFragment, index1, index2){
			/*var temp = bookmarksFragment[index1];
			bookmarksFragment[index1] = bookmarksFragment[index2];
			bookmarksFragment[index2] = temp;
			return this;*/
		}
		
		return {
			"object":bookmarks,
			"addBookmark":function(path, uri, title, icon_uri){
				var bookmark_path = path_resolver(path);
				var bookmark_obj = {};
				bookmark_obj[bookmark_uri_name] = uri;
				bookmark_obj[bookmark_title_name] = title;
				bookmark_obj[bookmark_iconuri_name] = icon_uri;
				bookmark_path[bookmarks_name].push(bookmark_obj);
				return this;
			},
			"addFolder":function(path, name){
				var bookmark_path = path_resolver(path);
				bookmark_path[folders_name][name] = {};
				bookmark_path[folders_name][name][folders_name] = {};
				bookmark_path[folders_name][name][bookmarks_name] = [];
				return this;
			},
			"removeBookmark":function(path, uri){
				var bookmark_path = path_resolver(path);
				delete bookmark_path[bookmarks_name][uri];
				return this;
			},
			"removeFolder":function(path, name){
				var bookmark_path = path_resolver(path);
				delete bookmark_path[folders_name][name];
				return this;
			}
			"swap": bookmark_swap,
			"move": function(path_from, index_from, path_to, index_to){
				/*var i = from;
				if(from < to){
					while(i < to){
						bookmark_swap(bookmarks, i, i+1); i++;
					}
				}
				else if(from > to){
					while(i > to){
						bookmark_swap(bookmarks, i, i-1); i--;
					}
				}
				return to;*/
			}
		
		}
	}
	
	
	
	
	
	function create_link_css(href){
		var link = document.createElement("link");
		link.setAttribute("rel",  "stylesheet");
		link.setAttribute("type", "text/css");
		link.setAttribute("href", href);
		document.getElementsByTagName("head")[0].appendChild(link);
	}
	
	
	
	
	
	return{
		"init":function(widgetID, secret){
			return {
				"Storage": {
					"localStorage": {
						/*"set"(key, value, callback) -> Storage.localStorage
						"get"(key, callback) -> value
						"delete"(key, callback) -> Storage.localStorage
						"deleteAll"(callback) -> Storage.localStorage
						"exists"(key, callback) -> bool*/
					},
					"remoteStorage": {
						"get":function(key, callback){
							precall(0, widgetID, secret, key, null, callback);
							return this; //API.Storage.remoteStorage;
						},
						"set":function(key, value, callback){
							precall(1, widgetID, secret, key, value, callback);
							return this; //API.Storage.remoteStorage;
						},
						"delete":function(key, callback){
							precall(2, widgetID, secret, key, null, callback);
							return this; //API.Storage.remoteStorage;
						}
						/*
						"deleteAll"(callback) -> Storage.remoteStorage
						"exists"(key, callback) -> bool*/
					},
					"sharedStorage": {
						"get":function(key, callback){
							precall(0, -1, null, key, null, callback);
							return this; //API.Storage.sharedStorage;
						},
						"set":function(key, value, callback){
							precall(1, -1, null, key, value, callback);
							return this; //API.Storage.sharedStorage;
						},
						"delete":function(key, callback){
							precall(2, -1, secret, key, null, callback);
							return this; //API.Storage.remoteStorage;
						}
						/*"exists"(key, callback) -> bool*/
					}
				},
				"Widget": {
					"create": function(){
						var div = document.createElement("div");
						div.style.display  = "block";
						div.style.position = "fixed";
						document.body.appendChild(div);
						div_base(div);
						return div;
					},
					"linkMyCSS": function(name){
						create_link_css(getUrl(widgetID, name));
						return this; //API.Widget
					},
					"linkExternalCSS": function(href){
						create_link_css(href);
						return this; //API.Widget
					}
				},
				"document": {
					"createElement": function(tagName){
						var elem = document.createElement(tagName);
						div_base(elem);
						return elem;
					}
				},
				"url": function(name){return getUrl(widgetID, name);},
				"Bookmarks": {
					"createObject": bookmarks_base
				}
			}
		}
	}
})();
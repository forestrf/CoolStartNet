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
	API.Bookmarks.createObject()
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
	function bookmarks_base(bookmarks){
		if(bookmarks === undefined){
			bookmarks = {};
			bookmarks["folders"]   = {};
			bookmarks["bookmarks"] = [];
		}
		
		//Given a path with the pattern a/b/c returns the object from the folder c inside of the b inside of the a inside of the root
		function path_resolver(path){
			path = path.split("/").reverse();
			var bookmark_path = bookmarks;
			while(path.length > 0){
				var path_fragment = path.pop();
				if(path_fragment !== ""){
					bookmark_path = bookmark_path["folders"][path_fragment];
				}
			}
			return bookmark_path;
		}
		
		return {
			"object": bookmarks,
			"addBookmark": function(path, uri, title, icon_uri){
				var real_path = path_resolver(path);
				if(!real_path){return this;}
				
				real_path["bookmarks"].push({
					type: "bookmark",
					uri: uri,
					title: title,
					iconuri: icon_uri
				});
				return this;
			},
			"addFolder": function(path, name){
				var real_path = path_resolver(path);
				if(!real_path){return this;}
				
				real_path["folders"][name] = {
					folders: {},
					bookmarks: []
				};
				
				real_path["bookmarks"].push({
					type: "folder",
					name: name
				});
				return this;
			},
			// Returns references that, if modified, the original object breaks?
			"getBookmark": function(path, index){
				var real_path = path_resolver(path);
				if(!real_path){return false;}
				
				if(real_path["bookmarks"][index] && real_path["bookmarks"][index]["type"] !== "folder"){
					return real_path["bookmarks"][index];
				}
				return false;
			},
			// Returns references that, if modified, the original object breaks?
			"getBookmarks": function(path){
				var real_path = path_resolver(path);
				if(!real_path){return false;}
				
				var result = [];
				var i = 0;
				while(i < real_path["bookmarks"].length){
					if(real_path["bookmarks"][i]["type"] !== "folder"){
						result.push(real_path["bookmarks"][i]);
					}
					i++;
				}
				return result;
			},
			// Returns references that, if modified, the original object breaks?
			"getFolders": function(path){
				var real_path = path_resolver(path);
				if(!real_path){return false;}
				
				var result = [];
				var i = 0;
				while(i < real_path["bookmarks"].length){
					if(real_path["bookmarks"][i]["type"] === "folder"){
						result.push(real_path["bookmarks"][i]["name"]);
					}
					i++;
				}
				return result;
			},
			// Returns references that, if modified, the original object breaks?
			"getElements": function(path){
				var real_path = path_resolver(path);
				if(!real_path){return false;}
				
				var result = [];
				var i = 0;
				while(i < real_path["bookmarks"].length){
					if(real_path["bookmarks"][i]["type"] === "folder"){
						result.push(real_path["bookmarks"][i]["name"]);
					}
					else if(real_path["bookmarks"][i]["type"] !== "folder"){
						result.push(real_path["bookmarks"][i]);
					}
					i++;
				}
				return result;
			},
			"removeBookmark": function(path, index, custom){
				if(custom === undefined){custom = false}
				var real_path = path_resolver(path);
				if(!real_path){return this;}
				
				if(custom || real_path["bookmarks"][index]["type"] === "bookmark"){
					real_path["bookmarks"].splice(index, 1);
				}
				return this;
			},
			"removeFolder": function(path, name_index){
				var real_path = path_resolver(path);
				if(!real_path){return this;}
				
				if(typeof name_index === "number"){
					if(real_path["bookmarks"][name_index]["type"] === "bookmark"){
						return this;
					}
					name_index = real_path["bookmarks"].splice(name_index, 1)[0]["name"];
				}
				
				delete real_path["folders"][name_index];
				return this;
			},
			// Moves a bookmark from one place to another. index starts from 0
			"moveBookmark": function(path_from, index_from, path_to, index_to, custom){
				if(custom === undefined){custom = false}
				var real_path_from = path_resolver(path_from);
				var real_path_to   = path_resolver(path_to);
				if(!real_path_from){return this;}
				if(!real_path_to){return this;}
				
				if(custom || real_path_from["bookmarks"][index_from]["type"] === "bookmark"){
					var bookmark = real_path_from["bookmarks"].splice(index_from ,1)[0];
					var temp = real_path_to["bookmarks"].splice(index_to);
					real_path_to["bookmarks"] = real_path_to["bookmarks"].concat(bookmark).concat(temp);
				}
				return this;
			},
			// Moves a bookmark from one place to another. index starts from 0
			"moveFolder": function(path_from, name_index_from, path_to, index_to){
				var real_path_from = path_resolver(path_from);
				var real_path_to   = path_resolver(path_to);
				if(!real_path_from){return this;}
				if(!real_path_to){return this;}
				
				var folder_bookmarks;
				
				if(typeof name_index_from === "number"){
					if(real_path_from["bookmarks"][name_index_from]["type"] !== "folder"){
						return this;
					}
					folder_bookmarks = real_path_from["bookmarks"].splice(name_index_from, 1)[0];
					name_index_from = folder_bookmarks["name"];
				}
				else{
					var i = 0;
					while(i < real_path_from["bookmarks"].length){
						if(real_path_from["bookmarks"][i]["name"] === name_index_from){
							if(real_path_from["bookmarks"][i]["type"] !== "folder"){
								return this;
							}
							folder_bookmarks = real_path_from["bookmarks"].splice(i, 1)[0];
							break;
						}
						i++;
					}
				}
				// name_index_from and folder_bookmarks setted at this point
				
				// move folder from bookmarks list
				var temp = real_path_to["bookmarks"].splice(index_to);
				real_path_to["bookmarks"] = real_path_to["bookmarks"].concat(folder_bookmarks).concat(temp);
				
				//move folder from folders list
				var folder = real_path_from["folders"][name_index_from];
				delete real_path_from["folders"][name_index_from];
				real_path_to["folders"][name_index_from] = folder;
				return this;
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
						"exists"(key, callback) -> bool
						"lastModified"(key, callback) -> //API.Storage.localStorage*/
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
						"deleteAll"(callback) -> //API.Storage.remoteStorage
						"exists"(key, callback) -> bool
						"lastModified"(key, callback) -> //API.Storage.remoteStorage*/
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
						/*"exists"(key, callback) -> bool
						"lastModified"(key, callback) -> //API.Storage.sharedStorage*/
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
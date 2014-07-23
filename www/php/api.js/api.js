/*
Consult pattern:

var consult = {
	'action'    : 'get',
	'widget'    : widgetID | 'global',
	'variables' : 'variable1'
}

var consult = {
	'action'    : 'get',
	'widget'    : widgetID | 'global',
	'variables' : ['variable1', ...]
}

var consult = {
	'action'    : 'set',
	'widget'    : widgetID | 'global',
	'variables' : {'variable1':'value1', ...}
}
*/

// 0 = get
// 1 = set
var API_F = (function(){
	var max_wait_GET_SET_request = [100, 100]; //ms
	var timeout_GET_SET = [0, 0];
	var callbacks_GET_SET_request = [[],[]];
	var next_GET_SET_request = [{}, {}];
	
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
		
		if(mode === 0 || mode === 1){
			
			if(next_GET_SET_request[mode][widgetID] === undefined){
				next_GET_SET_request[mode][widgetID] = {};
			}
			
			next_GET_SET_request[mode][widgetID][key] = value;
			
			
			callbacks_GET_SET_request[mode].push({"callback":callback,"widgetID":widgetID,"key":key});
			clearTimeout(timeout_GET_SET[mode]);
			if(mode === 0){
				timeout_GET_SET[mode] = setTimeout(execute_GET, max_wait_GET_SET_request[mode]);
			}
			else if(mode === 1){
				timeout_GET_SET[mode] = setTimeout(execute_SET, max_wait_GET_SET_request[mode]);
			}
		}
		else{
			callback(null);
		}
	}
	
	var execute = function(action, next_request, callbacksConsulta){
		var req = new XMLHttpRequest();
		req.open('POST', 'api.php', true);
		req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		req.onreadystatechange = function(aEvt){
			if(req.readyState == 4){
				if(req.status == 200){
					//console.log(req.responseText);
					var response = JSON.parse(req.responseText);
					
					// Go over the callbacks and generate a response
					if(response['response']==='OK'){
						for(var i in callbacksConsulta){
							
							var widgetID = callbacksConsulta[i]['widgetID'];
							var key    = callbacksConsulta[i]['key'];
							
							// If received
							if(typeof response['content'][widgetID] !== 'undefined'){
								// If key requested
								if(typeof response['content'][widgetID][key] !== 'undefined'){
									callbacksConsulta[i]['callback'](JSON.parse(response['content'][widgetID][key]));
								}
							}
						}
					}
					else{
						for(var i in callbacksConsulta){
							callbacksConsulta[i]['callback'](null);
						}
					}
				}
				else{
					for(var i in callbacksConsulta){
						callbacksConsulta[i]['callback'](null);
					}
				}
			}
		};
		var data = 'action='+['get', 'set'][action]+'&data='+encodeURIComponent(JSON.stringify(next_request));
		req.send(data);
	}
	
	
	// Execute and clean cache
	var execute_GET_SET = function(mode){
		execute(mode, next_GET_SET_request[mode], callbacks_GET_SET_request[mode]);
		next_GET_SET_request[mode] = {};
		callbacks_GET_SET_request[mode] = [];
	};
	var execute_GET = function(){execute_GET_SET(0);};
	var execute_SET = function(){execute_GET_SET(1);};
	
	
	
	// Return an url to get a file of the widget
	var getUrl = function(widgetID, filename){
		return 'widgetfile.php?widgetID='+widgetID+'&api=1&name='+escape(filename);
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
	
		function parseFloatRounded(number, roundedTo){
			return roundedTo === undefined ? parseFloat(number) : parseFloat(number).toFixed(roundedTo);
		}
	
		div["div"] = div;
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
			div.style.top = top + "%";
			return div;
		};
		div.setPosition["left"] = function(left){div.style.left = left + "%"; return div;};
		div.setPosition["top"] = function(top){div.style.top = top + "%"; return div;};
		
		div["getPosition"] = function(roundedTo){
			return {
				"left": parseFloatRounded(div.style.left.split("%")[0], roundedTo),
				"top":  parseFloatRounded(div.style.top.split("%")[0], roundedTo)
			};
		};
		div.getPosition["left"] = function(roundedTo){return parseFloatRounded(div.style.left.split("%")[0], roundedTo)};
		div.getPosition["top"] = function(roundedTo){return parseFloatRounded(div.style.top.split("%")[0], roundedTo)};
		
		div["setSize"] = function(width, height){
			div.style.width = width + "%";
			div.style.height = height + "%";
			return div;
		};
		div.setSize["width"] = function(width){div.style.width = width + "%"; return div;};
		div.setSize["height"] = function(height){div.style.height = height + "%"; return div;};
		
		div["getSize"] = function(roundedTo){
			return {
				"width":  parseFloatRounded(div.style.width.split("%")[0], roundedTo),
				"height": parseFloatRounded(div.style.height.split("%")[0], roundedTo)
			};
		};
		div.getSize["width"] = function(roundedTo){return parseFloatRounded(div.style.width.split("%")[0], roundedTo)};
		div.getSize["height"] = function(roundedTo){return parseFloatRounded(div.style.height.split("%")[0], roundedTo)};
		
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
	
	
	
	
	
	
	
	
	
	
	function Storage(widgetID, secret){
		return {
			"localStorage": {
				/*"set"(key, value, callback) -> Storage.localStorage
				"get"(key, callback) -> value
				"delete"(key, callback) -> Storage.localStorage
				"deleteAll"(callback) -> Storage.localStorage
				"exists"(key, callback) -> bool*/
			},
			"remoteStorage": {
				"set":function(key, value, callback){
					API_F.call(1, widgetID, secret, key, value, callback);
					return this; //API.Storage.remoteStorage;
				},
				"get":function(key, callback){
					API_F.call(0, widgetID, secret, key, null, callback);
					return this; //API.Storage.remoteStorage;
				}/*,
				"delete"(key, callback) -> Storage.remoteStorage
				"deleteAll"(callback) -> Storage.remoteStorage
				"exists"(key, callback) -> bool*/
			},
			"sharedStorage": {
				"set":function(key, value, callback){
					API_F.call(1, 'global', null, key, value, callback);
					return this; //API.Storage.sharedStorage;
				},
				"get":function(key, callback){
					API_F.call(0, 'global', null, key, null, callback);
					return this; //API.Storage.sharedStorage;
				}
				/*
				"delete"(key, callback) -> Storage.sharedStorage
				"exists"(key, callback) -> bool*/
			}
		}
	}
	
	
	
	
	
	
	
	function create_link_css(href){
		var link = document.createElement("link");
		link.setAttribute("rel", "stylesheet");
		link.setAttribute("type", "text/css");
		link.setAttribute("href", href);
		document.getElementsByTagName("head")[0].appendChild(link);
	}
	
	function Widget(widgetID, secret){
		return {
			"create": function(){
				var div = document.createElement("div");
				div.style.display = "block";
				div.style.position = "fixed";
				document.body.appendChild(div);
				API_F.div_base(div);
				return div;
			},
			"linkMyCSS": function(name){
				create_link_css(API_F.url(widgetID, name));
				return this; //API.Widget
			},
			"linkExternalCSS": function(href){
				create_link_css(href);
				return this; //API.Widget
			}
		}
	}
	
	
	
	
	
	
	
	
	
	return {
		"call": precall,
		"url": getUrl,
		"div_base": div_base,
		"Storage": Storage,
		"Widget": Widget,
		"document": Document
	};
})();








/*
Net;

Widget = (function(){
	function create(){
		
	}
	
	function includeCssFile(filename){
		var link = document.createElement("link");
		link.setAttribute("rel", "stylesheet");
		link.setAttribute("type", "text/css");
		link.setAttribute("href", API.url(widgetID, filename));
		document.getElementsByTagName("head")[0].appendChild(link);
	}
	
	return {
		"create":create,
		"includeCssFile":null
	}
})();
*/
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
	
	var precall = function(parameters, callback){
		var command = {
			'action':parameters['action'],
			'widgets':{}
		};
		command['widgets'][parameters['widget']] = parameters['variables'];
		
		call(command, callback);
	}
	
	var call = function(parameters, callback){
		if(
			parameters &&
			(parameters["action"] === 0 || parameters["action"] === 1) &&
			typeof parameters["widgets"] === "object"
		){
			// Una variable
			var mode = parameters["action"];
			var widgets = parameters["widgets"];
			for(var widget in widgets){
				if(typeof widgets[widget] === 'string'){
					widgets[widget] = [widgets[widget]];
				}
			}
			
			for(var widget in widgets){
				if(next_GET_SET_request[mode][widget] === undefined){
					next_GET_SET_request[mode][widget] = {};
				}
				for(var i in widgets[widget]){
					next_GET_SET_request[mode][widget][i] = widgets[widget][i];
				}
			}
			
			callbacks_GET_SET_request[mode].push({"callback":callback,"widgets":widgets});
			clearTimeout(timeout_GET_SET[mode]);
			if(mode === 0){
				timeout_GET_SET[mode] = setTimeout(execute_GET, max_wait_GET_SET_request[mode]);
			}
			else{
				timeout_GET_SET[mode] = setTimeout(execute_SET, max_wait_GET_SET_request[mode]);
			}
		}
		else{
			callback(undefined);
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
							
							var obj = {};
							var widget = '';
							
							// For each esquested widget
							for(widget in callbacksConsulta[i]['widgets']){
								// If received
								if(typeof response['content'][widget] !== 'undefined'){
									obj[widget] = {};
									// Object pattern {'var1':'', 'var2':''}
									for(var variable in callbacksConsulta[i]['widgets'][widget]){
										// If variable requested
										if(typeof response['content'][widget][variable] !== 'undefined'){
											obj[widget][variable] = JSON.parse(response['content'][widget][variable]);
										}
									}
								}
								callbacksConsulta[i]['callback'](obj[widget]);
							}
						}
					}
					else{
						for(var i in callbacksConsulta){
							callbacksConsulta[i]['callback'](undefined);
						}
					}
				}
				else{
					for(var i in callbacksConsulta){
						callbacksConsulta[i]['callback'](undefined);
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
	
	
	
	
	
	
	
	
	
	function widget_base(div){
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
		
		div["getPosition"] = function(){
			return {"left":div.style.left.split("%")[0], "top":div.style.top.split("%")[0]};
		};
		div.getPosition["left"] = function(left){return div.style.left.split("%")[0]};
		div.getPosition["top"] = function(top){return div.style.top.split("%")[0]};
		
		div["setSize"] = function(width, height){
			div.style.width = width + "%";
			div.style.height = height + "%";
			return div;
		};
		div.setSize["width"] = function(width){div.style.width = width + "%"; return div;};
		div.setSize["height"] = function(height){div.style.height = height + "%"; return div;};
		
		div["getSize"] = function(){
			return {"width":div.style.width.split("%")[0], "height":div.style.height.split("%")[0]};
		};
		div.getSize["width"] = function(width){return div.style.width.split("%")[0]};
		div.getSize["height"] = function(height){return div.style.height.split("%")[0]};
		
		div["setPositionSize"] = function(left, top, width, height){
			return div.setPosition(left, top).setSize(width, height);
		};
		
		div["getPositionSize"] = function(left, top, width, height){
			var p = div.getPosition();
			var s = div.getSize();
			return {
				"left": p.left,
				"top": p.top,
				"width": s.width,
				"height": s.height
			}
		};
		
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
	
	
	
	
	
	
	
	
	
	
	function Storage(widgetID){
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
					var c = {}; c[key] = value;
					var command = {'action':1,'widget':widgetID,'variables':c};
					API_F.call(command, function(e){callback(e[key]);});
					return this; //API.Storage.remoteStorage;
				},
				"get":function(key, callback){
					var c = {}; c[key] = null;
					var command = {'action':0,'widget':widgetID,'variables':c};
					API_F.call(command, function(e){callback(e[key]);});
					return this; //API.Storage.remoteStorage;
				}/*,
				"delete"(key, callback) -> Storage.remoteStorage
				"deleteAll"(callback) -> Storage.remoteStorage
				"exists"(key, callback) -> bool*/
			},
			"sharedStorage": {
				"set":function(key, value, callback){
					var c = {}; c[key] = value;
					var command = {'action':1,'widget':'global','variables':c};
					API_F.call(command, function(e){callback(e[key]);});
					return this; //API.Storage.sharedStorage;
				},
				"get":function(key, callback){
					var c = {}; c[key] = null;
					var command = {'action':0,'widget':'global','variables':c};
					API_F.call(command, function(e){callback(e[key]);});
					return this; //API.Storage.sharedStorage;
				}
				/*
				"delete"(key, callback) -> Storage.sharedStorage
				"exists"(key, callback) -> bool*/
			}
		}
	}
	
	
	
	
	
	
	
	
	
	Widget = {
		"create": function(){
			var div = document.createElement("div");
			div.style.display = "block";
			div.style.position = "fixed";
			document.body.appendChild(div);
			API_F.widget_base(div);
			return div;
		}
	}
	
	
	
	
	
	
	
	
	
	return {
		"call": precall,
		"url": getUrl,
		"widget_base": widget_base,
		"Storage": Storage,
		"Widget": Widget
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
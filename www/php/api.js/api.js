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
			timeout_GET_SET[mode] = mode === 0 ?
				setTimeout(execute_GET, max_wait_GET_SET_request[mode]) :
				setTimeout(execute_SET, max_wait_GET_SET_request[mode]);
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
							callbacksConsulta[i]['callback'](response);
						}
					}
				}
				else{
					console.log("Error loading page");
				}
			}
		};
		var actions = ['get', 'set'];
		var data = 'action='+actions[action]+'&data='+encodeURIComponent(JSON.stringify(next_request));
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
	
	
	return {
		"call":precall,
		"url":getUrl
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
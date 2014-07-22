/*
1000 => Attribute "action" with value "set" or "get" not present
1001 => "call" needs to be called with a "Consult" object
1002 => Attribute "action" not present or not a string
1003 => Attribute "widget" not present or incorrect
*/

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
		if(parameters){
			if(parameters["action"] === 0 || parameters["action"] === 1){
				if(typeof parameters["widgets"] === "object"){
					// Una variable
					get_or_set(parameters["action"], callback, parameters["widgets"]);
				}
				else{
					callback(fail(1003));
				}
			}
			else{
				callback(fail(1002));
			}
		}
		else{
			callback(fail(1001));
		}
	}
	
	var get_or_set = function(mode, callback, widgets){
		for(var widget in widgets){
			if(typeof widgets[widget] === 'string'){
				widgets[widget] = [widgets[widget]];
			}
		}
		if(mode === 0 || mode === 1){
			for(var widget in widgets){
				add_to_GET_SET_request(widget, widgets[widget], mode);
			}
			callbacks_GET_SET_request[mode].push({"callback":callback,"widgets":widgets});
			clearTimeout(timeout_GET_SET[mode]);
			timeout_GET_SET[mode] = setTimeout(function(){execute_GET_SET(mode);}, max_wait_GET_SET_request[mode]);
		}
		else{
			callback(fail(1000));
		}
	}
	
	// get => array_variables -> string | []
	// set => array_variables -> {}
	var add_to_GET_SET_request = function(widget, array_variables, mode){
		if(next_GET_SET_request[mode][widget] === undefined){
			next_GET_SET_request[mode][widget] = {};
		}
		for(var i in array_variables){
			if(mode === 0){
				next_GET_SET_request[mode][widget][array_variables[i]] = null;
			}
			else if(mode === 1){
				next_GET_SET_request[mode][widget][i] = array_variables[i];
			}
		}
	}
	
	var fail = function(n){
		return {'response':'FAIL',"content":n};
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
									switch(action){
										case 0:
											// Array pattern ['var1', 'var2']
											for(var j in callbacksConsulta[i]['widgets'][widget]){
												var variable = callbacksConsulta[i]['widgets'][widget][j];
												// If variable requested
												if(typeof response['content'][widget][variable] !== 'undefined'){
													obj[widget][variable] = response['content'][widget][variable];
												}
											}
										break;
										case 1:
											// Object pattern {'var1':'', 'var2':''}
											for(var variable in callbacksConsulta[i]['widgets'][widget]){
												// If variable requested
												if(typeof response['content'][widget][variable] !== 'undefined'){
													obj[widget][variable] = response['content'][widget][variable];
												}
											}
										break;
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
	}
	
	
	
	
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
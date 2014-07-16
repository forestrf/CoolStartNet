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


API = (function(){
	var max_wait_GET_request = 100; //ms
	var max_wait_SET_request = 100; //ms
	
	var timeout_GET = 0;
	var timeout_SET = 0;
	
	var callbacks_GET_request = [];
	var callbacks_SET_request = [];
	
	var next_GET_request = {};
	var next_SET_request = {};
	
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
			if(typeof parameters["action"] === "string"){
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
		switch(mode){
			case 'get':
				for(var widget in widgets){
					add_to_GET_request(widget, widgets[widget]);
				}
				callbacks_GET_request.push({"callback":callback,"widgets":widgets});
				clearTimeout(timeout_GET);
				timeout_GET = setTimeout(execute_GET, max_wait_GET_request);
			break;
			case 'set':
				for(var widget in widgets){
					add_to_SET_request(widget, widgets[widget]);
				}
				callbacks_SET_request.push({"callback":callback,"widgets":widgets});
				clearTimeout(timeout_SET);
				timeout_SET = setTimeout(execute_SET, max_wait_SET_request);
			break;
			default:
				callback(fail(1000));
			break;
		}
	}
	
	// array_variables -> string | []
	var add_to_GET_request = function(widget, array_variables){
		if(next_GET_request[widget] === undefined){
			next_GET_request[widget] = {};
		}
		for(var i in array_variables){
			next_GET_request[widget][array_variables[i]] = null;
		}
	}
	
	// array_variables -> {}
	var add_to_SET_request = function(widget, array_variables){
		if(next_SET_request[widget] === undefined){
			next_SET_request[widget] = {};
		}
		for(var i in array_variables){
			next_SET_request[widget][i] = array_variables[i];
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
										case 'get':
											// Array pattern ['var1', 'var2']
											for(var j in callbacksConsulta[i]['widgets'][widget]){
												var variable = callbacksConsulta[i]['widgets'][widget][j];
												// If variable requested
												if(typeof response['content'][widget][variable] !== 'undefined'){
													obj[widget][variable] = response['content'][widget][variable];
												}
											}
										break;
										case 'set':
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
		var data = 'action='+action+'&data='+encodeURIComponent(JSON.stringify(next_request));
		req.send(data);
	}
	
	// Execute and clean cache
	var execute_GET = function(){
		execute('get', next_GET_request, callbacks_GET_request);
		next_GET_request = {};
		callbacks_GET_request = [];
	}
	
	// Execute and clean cache
	var execute_SET = function(){
		execute('set', next_SET_request, callbacks_SET_request);
		next_SET_request = {};
		callbacks_SET_request = [];
	}
	
	// Return an url to get a file of the widget
	var getUrl = function(widget, filename){
		return 'widgetfile.php?widgetID='+widget+'&api=1&name='+escape(filename);
	}
	
	return {
		"call":precall,
		"url":getUrl
	};
})();
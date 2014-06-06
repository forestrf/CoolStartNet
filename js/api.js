/*
D.g('enviar').onclick = function(){
	var consulta = {
		'widget':Array(D.g('widget').value),
		'variable':Array(Array(D.g('variable').value)),
		'action':D.g('action').value
	}
	console.log(consulta);
	var req = new XMLHttpRequest();
	req.open('GET', 'http://localhost/api.php?size=1&widget='+JSON.stringify(consulta['widget'])+'&variable='+JSON.stringify(consulta['variable'])+'&action=get', false); 
	req.send(null);
	if(req.status == 200)
		console.log(req.responseText);
}
*/

/*
1000 => Debe especificarse un atributo "action" con valor "set" o "get"
1001 => Debe de llamarse a call con un objeto consulta
1002 => debe de especificarse un action en el objeto consulta
1003 => debe de especificarse un nombre de widget en el objeto consulta
1004 => debe de especificarse una variable o un array de variables en el objeto consulta
*/


API = (function(){
	var call = function(parametros, callback){
		if(parametros){
			if(typeof parametros["action"] === "string"){
				if(typeof parametros["widget"] === "string"){
					if(typeof parametros["variable"] === "object"){
						// Varias variables
						get_o_set(parametros["action"], callback, parametros["widget"], parametros["variable"], parametros["value"]);
					}
					else if(typeof parametros["variable"] === "string"){
						// Una variable
						get_o_set(parametros["action"], callback, parametros["widget"], [parametros["variable"]], [parametros["value"]]);
					}
					else{
						callback(fail(1004));
					}
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
	
	var get_o_set = function(modo, callback, widget, variables, valores){
		switch(modo){
			case 'get':
				agregaAConsultaGet(widget, variables);
				callbacksConsultaGet.push({"callback":callback,"widget":widget,"variables":variables});
			break;
			case 'set':
				agregaAConsultaSet(widget, variables, valores);
				callbacksConsultaSet.push({"callback":callback,"widget":widget});
			break;
			default:
				callback(fail(1000));
			break;
		}
	}
	
	
	
	
	// OK
	// string, []
	var agregaAConsultaGet = function(widget, array_variables){
		if(proximaConsultaGet[widget] === undefined){
			proximaConsultaGet[widget] = [];
		}
		for(var i in array_variables){
			proximaConsultaGet[widget][array_variables[i]] = null;
		}
	}
	
	// OK
	// string, [], []
	var agregaAConsultaSet = function(widget, array_variables, array_valores){
		if(proximaConsultaSet[widget] === undefined){
			proximaConsultaSet[widget] = [];
		}
		for(var i in array_variables){
			proximaConsultaSet[widget][array_variables[i]] = array_valores[i];
		}
	}
	
	
	
	
	var callbacksConsultaGet = [];
	var callbacksConsultaSet = [];
	
	var proximaConsultaGet = {};
	var proximaConsultaSet = {};
	
	var fail = function(n){
		return {'response':'FAIL',"content":n};
	}
	
	
	var procesaGet = function(){
		var widget = [];
		var variables = [];
		for(var i in proximaConsultaGet){
			widget.push(i);
			var variables_temp = [];
			for(var j in proximaConsultaGet[i]){
				variables_temp.push(j);
			}
			variables.push(variables_temp);
		}
		
		var req = new XMLHttpRequest();
		req.open('GET', 'api.php?size='+widget.length+'&widget='+JSON.stringify(widget)+'&variable='+JSON.stringify(variables)+'&action=get', true);
		req.onreadystatechange = function(aEvt){
			if(req.readyState == 4){
				if(req.status == 200)
					console.log(req.responseText);
				else
					console.log("Error loading page\n");
			}
		};
		req.send(null);
		
		
	}
	
	
	
	return {
		"call":call,
		"proximaConsultaGet":proximaConsultaGet,
		"proximaConsultaSet":proximaConsultaSet,
		"procesaGet":procesaGet
	};
})();
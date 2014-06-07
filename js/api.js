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
*/

/*
parametros => (
	action => get/set,
	widget => (
		'widget1' => (
			'variable1'
		)
	)
)
*/


API = (function(){
	var call = function(parametros, callback){
		if(parametros){
			if(typeof parametros["action"] === "string"){
				if(typeof parametros["widgets"] === "object"){
					// Una variable
					get_o_set(parametros["action"], callback, parametros["widgets"]);
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
	
	var get_o_set = function(modo, callback, widgets){
		switch(modo){
			case 'get':
				for(var widget in widgets){
					agregaAConsultaGet(widget, widgets[widget]);
				}
				callbacksConsultaGet.push({"callback":callback,"widgets":widgets});
			break;
			case 'set':
				for(var widget in widgets){
					agregaAConsultaSet(widget, widgets[widget]);
				}
				callbacksConsultaSet.push({"callback":callback,"widgets":widgets});
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
			proximaConsultaGet[widget] = {};
		}
		for(var i in array_variables){
			proximaConsultaGet[widget][array_variables[i]] = null;
		}
	}
	
	// OK
	// string, [], []
	var agregaAConsultaSet = function(widget, array_variables){
		if(proximaConsultaSet[widget] === undefined){
			proximaConsultaSet[widget] = {};
		}
		for(var i in array_variables){
			proximaConsultaSet[widget][i] = array_variables[i];
		}
	}
	
	
	
	
	var callbacksConsultaGet = [];
	var callbacksConsultaSet = [];
	
	var proximaConsultaGet = {};
	var proximaConsultaSet = {};
	
	var fail = function(n){
		return {'response':'FAIL',"content":n};
	}
	
	
	var procesa = function(action, proximaConsulta, callbacksConsulta){
		var req = new XMLHttpRequest();
		req.open('POST', 'api.php', true);
		req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		req.onreadystatechange = function(aEvt){
			if(req.readyState == 4){
				if(req.status == 200){
					//console.log(req.responseText);
					switch(action){
						case 'get':
							var respuesta = JSON.parse(req.responseText);
							
							//{"callback":callback,"widgets":widgets,"variables":variables}
							
							// Recorrer los callback y generar respuesta
							if(respuesta['response']==='OK'){
								for(var i in callbacksConsulta){
									
									var obj = {};
									
									// Por cada widget pedido
									for(var widget in callbacksConsulta[i]['widgets']){
										// Si está recibido
										if(typeof respuesta['content'][widget] !== 'undefined'){
											obj[widget] = {};
											for(var j in callbacksConsulta[i]['widgets'][widget]){
												var variable = callbacksConsulta[i]['widgets'][widget][j];
												// Si se pidió la variable
												if(typeof respuesta['content'][widget][variable] !== 'undefined'){
													obj[widget][variable] = respuesta['content'][widget][variable];
												}
											}
										}
									}
									
									callbacksConsulta[i]['callback'](obj);
								}
							}
							else{
								for(var i in callbacksConsulta){
									callbacksConsulta[i]['callback'](respuesta);
								}
							}
							
							
							
						break;
						case 'set':
							
						break;
					}
					if(action === 'get'){
					
					}
				}
				else
					console.log("Error loading page\n");
			}
		};
		var data = 'action='+action+'&data='+encodeURIComponent(JSON.stringify(proximaConsulta));
		req.send(data);
	}
	
	var procesaGet = function(){
		procesa('get', proximaConsultaGet, callbacksConsultaGet);
		proximaConsultaGet = {};
		callbacksConsultaGet = [];
	}
	
	var procesaSet = function(){
		procesa('set', proximaConsultaSet, callbacksConsultaSet);
		proximaConsultaSet = {};
		callbacksConsultaSet = [];
	}
	
	
	
	return {
		"call":call,
		"proximaConsultaGet":proximaConsultaGet,
		"proximaConsultaSet":proximaConsultaSet,
		"procesaGet":procesaGet,
		"procesaSet":procesaSet,
		"callbacksConsultaGet":callbacksConsultaGet
	};
})();
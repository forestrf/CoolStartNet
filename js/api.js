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
	var precall = function(parametros, callback){
				
		var comando = {
			'action':parametros['action'],
			'widgets':{}
		};
		comando['widgets'][parametros['widget']] = parametros['variables'];
		
		call(comando, callback);
	}
	
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
		for(var widget in widgets){
			if(typeof widgets[widget] === 'string'){
				widgets[widget] = [widgets[widget]];
			}
		}
		switch(modo){
			case 'get':
				for(var widget in widgets){
					agregaAConsultaGet(widget, widgets[widget]);
				}
				callbacksConsultaGet.push({"callback":callback,"widgets":widgets});
				clearTimeout(timeoutGet);
				timeoutGet = setTimeout(procesaGet, 100);
			break;
			case 'set':
				for(var widget in widgets){
					agregaAConsultaSet(widget, widgets[widget]);
				}
				callbacksConsultaSet.push({"callback":callback,"widgets":widgets});
				clearTimeout(timeoutSet);
				timeoutSet = setTimeout(procesaSet, 100);
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
	
	
	
	var timeoutGet = 0;
	var timeoutSet = 0;
	
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
							
							// Recorrer los callback y generar respuesta
							if(respuesta['response']==='OK'){
								for(var i in callbacksConsulta){
									
									var obj = {};
									var widget_contador = 0;
									var widget = '';
									
									// Por cada widget pedido
									for(widget in callbacksConsulta[i]['widgets']){
										++widget_contador;
										// Si est� recibido
										if(typeof respuesta['content'][widget] !== 'undefined'){
											obj[widget] = {};
											for(var j in callbacksConsulta[i]['widgets'][widget]){
												var variable = callbacksConsulta[i]['widgets'][widget][j];
												// Si se pidi� la variable
												if(typeof respuesta['content'][widget][variable] !== 'undefined'){
													obj[widget][variable] = respuesta['content'][widget][variable];
												}
											}
										}
									}
									
									if(widget_contador === 1){
										callbacksConsulta[i]['callback'](obj[widget]);
									}
									else{
										callbacksConsulta[i]['callback'](obj);
									}
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
		"call":precall,
	};
})();
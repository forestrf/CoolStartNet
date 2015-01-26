var IPA = (function(){
	
	
	
	return{
		"init":function(server_vars){
			if (undefined === server_vars) {server_vars = {};}
			
			return {
				"widgetImage": function(widgetID, filename){
					return '//' + server_vars.WEB_PATH + 'widgetfile/' + widgetID + '/static/' + encodeURIComponent(filename);
				}
			};
		}
	};
})();

var IPA = (function(){
	
	
	
	return{
		"init":function(server_vars){
			if (undefined === server_vars) {server_vars = {};}
			
			return {
				"widgetImage": function(widgetID){
					return '//' + server_vars.WEB_PATH + 'widgetImagePreview?id=' + widgetID;
				}
			}
		}
	}
})();

var types = ["variables","functions","static functions","objects"];

// types: variable(s), function(s), object(s)
function path_resolver(path, json){
	if(path === ""){
		return {"json": json, "type": "objects"};
	}
	path = path.split("/").reverse();
	var json_path = json;
	while(path.length > 0){
		var path_fragment = path.pop();
		if(path_fragment !== ""){
			var sub_path = ({"v":"variables","f":"functions","s":"static functions","o":"objects"})[path_fragment[0]];
			var name_path = path_fragment;
			if(sub_path){
				json_path = json_path[sub_path];
				name_path = path_fragment.substr(sub_path.length).replace("-","");
			}
			for(var i = 0; i < json_path.length; i++){
				if(json_path[i].name === name_path){
					json_path = json_path[i];
					break;
				}
			}
		}
	}
	if(sub_path){
		if(json_path instanceof Array){
			return {"json": json_path, "type": ({"v":"variables","f":"functions","s":"functions","o":"objects"})[sub_path[0]]};
		} else {
			return {"json": json_path, "type": ({"v":"variable","f":"function","s":"function","o":"object"})[sub_path[0]]};
		}
	} else {
		return {"json": json_path, "type": "object"};
	}
}
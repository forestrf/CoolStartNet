var DOC = 
{"objects":[{"name":"API","description":"Set of functions that allows the use of the web database, widget control on the screen and more","fast_description":"Set of functions that allows the use of the web database, widget control on the screen and more","objects":[{"name":"storage","description":"Objects and functions to manage the browser and server database to set and get variables","fast_description":"Objects and functions to manage the browser and server database to set and get variables","objects":[{"name":"localStorage","description":"functions to manage (set, get and delete) data from the browser localStorage.\nThe browser localStorage can be accessed and modified by every widget. It means that this data can't be trusted.\n\nKeys in the database are stored as `widgetID-key` to prevent problems with different widgets having the same key.","fast_description":"functions to manage data from the browser localStorage.","functions":[{"name":"get","return":"object","parameters":[{"name":"key","type":"string","explanation":"String key to search on the database"},{"name":"callback","type":"function","default":"function(data){}","explanation":"The function takes the object from the database as a parameter"}],"fast_description":"Get the value corresponding to `key` stored in the browser localStorage","description":"Get the value corresponding to `key` stored in the browser localStorage.\n\n`callback` takes as parameter a variable when there is data stored for the key or `null` when there is no data stored for the key.\nThe object `data` is the same object saved by the [set function](#objects-API/objects-storage/objects-localStorage/functions-set).\n\nThe key is saved in the browser localStorage as `widgetID-key` to allow two or more widgets to have the same key name.\n\nReturns [API.storage.localStorage](#objects-API/objects-storage/objects-localStorage).\n\n\n```javascript\nAPI.storage.localStorage.get('some key', function(data){\n\tif(data !== null){\n\t\tconsole.log(data);\n\t} else {\n\t\tconsole.log('No data stored under this key');\n\t}\n}\n```"},{"name":"set","return":"object","description":"Set a value corresponding to `key` stored in the browser local Storage. The variable is saved as JSON internally and the JSON is parsed when the variable is recovered, so this function can save objects.\n\n`callback` takes as parameter `true` or `false` depending if the operation was successful or not.\nThe object `data` can be recovered using the [get function](#objects-API/objects-storage/objects-localStorage/functions-get).\n\nThe key is saved in the browser localStorage as `widgetID-key` to allow two or more widgets to have the same key name.\n\nReturns [API.storage.localStorage](#objects-API/objects-storage/objects-localStorage).\n\n\n```javascript\nAPI.storage.localStorage.set('some key', 'some value', function(state){\n\tif(state){\n\t\tconsole.log('Saved');\n\t} else {\n\t\tconsole.log('Not saved');\n\t}\n}\n```","fast_description":"Set a value corresponding to `key` stored in the browser localStorage","parameters":[{"name":"key","type":"string","explanation":"String key to save on the database"},{"name":"data","type":"object","explanation":"variable to save"},{"name":"callback","type":"function","default":"function(state){}","explanation":"The function takes `true`(data saved) or `false`(data not saved)"}]},{"name":"delete","return":"object","description":"Delete a key (with its value) from the browser local Storage.\n\n`callback` takes as parameter `true` or `false` depending if the operation was successful or not.\n\nReturns [API.storage.localStorage](#objects-API/objects-storage/objects-localStorage).\n\n\n```javascript\nAPI.storage.localStorage.delete('some key', function(state){\n\tif(state){\n\t\tconsole.log('Deleted');\n\t} else {\n\t\tconsole.log('Not deleted');\n\t}\n}\n```","fast_description":"Delete a `key` and its value stored in the browser localStorage","parameters":[{"name":"key","type":"string","explanation":"String key to delete from the database"},{"name":"callback","type":"function","default":"function(state){}","explanation":"The function takes `true`(data deleted) or `false`(data not deleted)"}]},{"name":"deleteAll","return":"object","description":"Deletes all the keys (with its values) from the browser local Storage that correspond to the widget.\n\n`callback` takes as parameter `true` or `false` depending if the operation was successful or not.\n\nReturns [API.storage.localStorage](#objects-API/objects-storage/objects-localStorage).\n\n\n```javascript\nAPI.storage.localStorage.deleteAll(function(state){\n\tif(state){\n\t\tconsole.log('All deleted');\n\t} else {\n\t\tconsole.log('All Not deleted');\n\t}\n}\n```","fast_description":"Deletes all `key` and its values stored in the browser localStorage","parameters":[{"name":"callback","type":"function","default":"function(state){}","explanation":"The function takes `true`(all data deleted) or `false`(all data not deleted)"}]},{"name":"exists","return":"object","description":"Checks if a key is present in the browser local Storage.\n\n`callback` takes as parameter `true` or `false` depending if the key is present or not.\n\nReturns [API.storage.localStorage](#objects-API/objects-storage/objects-localStorage).\n\n\n```javascript\nAPI.storage.localStorage.exists('some key', function(state){\n\tif(state){\n\t\tconsole.log('The key exists');\n\t} else {\n\t\tconsole.log('The key Not exists');\n\t}\n}\n```","fast_description":"Checks if `key` exists in the browser localStorage","parameters":[{"name":"callback","type":"function","default":"function(result){}","explanation":"The function takes `true`(the key exists) or `false`(the key not exists)"}]}]},{"name":"remoteStorage","description":"functions to manage (set, get and delete) data from the server database.\n\nThis data cannot be accessed (read or write) by other widgets. It works like a private server database.","fast_description":"functions to manage data from the private server database. Only accessible by the current widget","functions":[{"name":"get","return":"object","description":"Get the value corresponding to `key` stored in the private server database.\n\n`callback` takes as parameter a variable when there is data stored for the key or `null` when there is no data stored for the key.\nThe object `data` is the same object saved by the [set function](#objects-API/objects-storage/objects-remoteStorage/functions-set).\n\nThe key is saved in the server database and cannot be accessed by other widgets.\n\nReturns [API.storage.remoteStorage](#objects-API/objects-storage/objects-remoteStorage).\n\n\n```javascript\nAPI.storage.remoteStorage.get('some key', function(data){\n\tif(data !== null){\n\t\tconsole.log(data);\n\t} else {\n\t\tconsole.log('No data stored under this key');\n\t}\n}\n```","fast_description":"Get the value corresponding to `key` stored in the private server database"}]}]}]}]}

;

/*
var nicaso = {"storage": {
	"remoteStorage": {
		"get": function(key, callback){
			precall(0, widgetID, secret, key, null, callback);
			return this; //API.Storage.remoteStorage;
		},
		"set": function(key, value, callback){
			precall(1, widgetID, secret, key, value, callback);
			return this; //API.Storage.remoteStorage;
		},
		"delete": function(key, callback){
			precall(2, widgetID, secret, key, null, callback);
			return this; //API.Storage.remoteStorage;
		},
		"deleteAll": function(callback){
			precall(3, widgetID, secret, null, null, callback);
			return this; //API.Storage.remoteStorage;
		},
		"exists": function(key, callback){
			precall(4, widgetID, secret, key, null, callback);
			return this; //API.Storage.remoteStorage;
		}
	},
	"sharedStorage": {
		"get": function(key, callback){
			precall(0, -1, null, key, null, callback);
			return this; //API.Storage.sharedStorage;
		},
		"set": function(key, value, callback){
			precall(1, -1, null, key, value, callback);
			return this; //API.Storage.sharedStorage;
		},
		"delete": function(key, callback){
			precall(2, -1, null, key, null, callback);
			return this; //API.Storage.remoteStorage;
		},
		"exists": function(key, callback){
			precall(4, -1, null, key, null, callback);
			return this; //API.Storage.remoteStorage;
		}
	}
},
"widget": {
	"create": function(){
		var div = document.createElement("div");
		div.style.display  = "block";
		div.style.position = "fixed";
		document.body.appendChild(div);
		div_base(div);
		return div;
	},
	"linkMyCSS": function(name){
		create_link_css(getUrl(widgetID, name));
		return this; //API.Widget
	},
	"linkExternalCSS": function(href){
		create_link_css(href);
		return this; //API.Widget
	}
},
"document": {
	"createElement": function(tagName){
		var elem = document.createElement(tagName);
		div_base(elem);
		return elem;
	}
},
"url": function(name){return getUrl(widgetID, name);},
"bookmarks": {
	"createObject": bookmarks_base
}}
*/
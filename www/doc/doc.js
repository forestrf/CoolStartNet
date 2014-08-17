var DOC = 
{"objects":[{"name":"API","description":"Set of functions that allows the use of the web database, widget control on the screen and more","fast_description":"Set of functions that allows the use of the web database, widget control on the screen and more","objects":[{"name":"storage","description":"Objects and functions to manage the browser and server database to set and get variables","fast_description":"Objects and functions to manage the browser and server database to set and get variables","objects":[{"name":"localStorage","description":"functions to set and get data from the browser local storage. This data can be modified by every widget","fast_description":"functions to set and get data from the browser local storage. This data can be modified by every widget","functions":[{"name":"get","return":"object","parameters":[{"name":"key","type":"string","explanation":"String key to search on the database"},{"name":"callback","type":"function","default":"function(data){}","explanation":"The function takes the object from the database as a parameter"}],"fast_description":"Get the value corresponding to `key` stored in the browser localStorage","description":"Get the value corresponding to `key` stored in the browser localStorage.\n\n`callback` takes as parameter a variable when there is data stored for the key or `null` when there is no data stored for the key.\nThe object `data` is the same object saved by the [set function](#API/objects-storage/objects-localStorage/functions-set).\n\nThe key is saved in the browser localStorage as `widgetID-key` to allow two or more widgets to have the same key name.\n\nReturns [API.storage.localStorage](#objects-API/objects-storage/objects-localStorage).\n\n\n```javascript\nAPI.storage.localStorage.get('some key', function(data){\n\tif(data !== undefined){\n\t\tconsole.log(data);\n\t} else {\n\t\tconsole.log('No data stored under this key');\n\t}\n}\n```"}]}]}]}]}

;

/*
var nicaso = {"storage": {
	"localStorage": {
		"get": function(key, callback){
			local_precall(0, widgetID, key, null, callback);
			return this; //API.Storage.localStorage;
		},
		"set": function(key, value, callback){
			local_precall(1, widgetID, key, value, callback);
			return this; //API.Storage.localStorage;
		},
		"delete": function(key, callback){
			local_precall(2, widgetID, key, null, callback);
			return this; //API.Storage.localStorage;
		},
		"deleteAll": function(callback){
			local_precall(3, widgetID, null, null, callback);
			return this; //API.Storage.remoteStorage;
		},
		"exists": function(key, callback){
			local_precall(4, widgetID, key, null, callback);
			return this; //API.Storage.localStorage;
		}
	},
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
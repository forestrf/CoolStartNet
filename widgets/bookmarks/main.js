var bookmarks = API.Bookmarks.createObject();

API.Storage.sharedStorage.get('bookmarks', function(entrada){
	if(entrada){
		bookmarks.object = entrada;
	}
	else{
		bookmarks.addBookmark("/", "http://1")
		.addFolder("/", "first folder")
		.addBookmark("/first folder", "http://2")
		.addBookmark("/first folder", "http://3")
		.addBookmark("/first folder", "http://4")
		.addBookmark("/first folder", "http://5")
		.addFolder("/", "second folder")
		.addFolder("/second folder/", "third folder")
		.addBookmark("/second folder/third folder/", "http://6")
		.moveBookmark("/first folder",2,"/second folder/third folder",0)
		.moveFolder("/second folder","third folder","/",0)
		.moveFolder("/",3,"/third folder",0);
	}
	
	// do not show the parent folder (first child) brcause it is a inexistent folder created by recursive_bookmark_parser.
	recursive_bookmark_parser(ventana, "", bookmarks.getElements(""));
});

var configs = {
	left: 5,
	top: 55,
	width: 20,
	height: 40
};

API.Storage.remoteStorage.get('configs', function(entrada){
	if(entrada){
		configs = entrada;
	}
});

// Import css
API.Widget.linkMyCSS('css.css');

//crel2 shortcut
var C = crel2;

// px sizes for the expandable folder functions
var folderSize = 30;
var bookmarSize = 30;

// Creating the widget
var ventana = API.Widget.create();
ventana.addClass("bookmarks");

// Setting size and position
ventana.setPositionSize(configs.left, configs.top, configs.width, configs.height);

// Setting background color
ventana.style.backgroundColor = "rgba(0, 0, 0, 0.7)";

function recursive_bookmark_parser(element, path, elements){
	var height = 0;
	var i = 0;
	
	while(i < elements.length){
		if(typeof elements[i] === "string"){
			// Folder
			var folder_obj = C('div', ['class', 'folder', 'style', 'height: 0px;']);
			var folderTitle = C('div', ['class','foldername'], elements[i])
			element.appendChild(folderTitle);
			
			var folder_height = recursive_bookmark_parser(folder_obj, path+"/"+elements[i], bookmarks.getElements(path+"/"+elements[i]));
			element.appendChild(folder_obj);
			height += folderSize + folder_height;
			
			folderTitle.onclick = (function(height, folder){
				return function(){
					folder.style.height = folder.style.height === "0px" ? height+"px" : "0px";
				}
			})(folder_height, folder_obj);
		}
		else if(elements[i].type === "bookmark"){
			// Bookmark
			var a = C("a", ["href", elements[i].uri, "style", "background-color: rgba(255, 255, 255, 0.14);"], elements[i].title ? elements[i].title : elements[i].uri);
			if(elements[i].iconuri){
				a.style.backgroundImage = "url('"+elements[i].iconuri+"')";
			}
			element.appendChild(a);
			height += bookmarSize;
		}
		i++;
	}
	
	return height;
}

// Function for the config widgetID. It returns the html object to append on the config window
var CONFIG_function = function(functions){
	function realTimeMove(data){
		ventana.setPositionSize(data.left, data.top, data.width, data.height);
	}
	
	functions.positioning(
		{
			"width"   : configs.width,
			"height"  : configs.height,
			"left"    : configs.left,
			"top"     : configs.top,
			"show_bg" : false,
			"realtime": realTimeMove
		},
		console.log
	);
	
	
}

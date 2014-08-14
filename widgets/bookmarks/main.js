// Import css
API.widget.linkMyCSS('css.css').linkExternalCSS("//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css");

//crel2 shortcut
var C = crel2;

// Creating the widget
var ventana = API.widget.create();
ventana.addClass("bookmarks");

// Bookmarks object and more variables
var bookmarks = API.bookmarks.createObject();
var buttonEdit = C('div', ['class', 'fa fa-pencil-square button_add']);

API.storage.sharedStorage.get('bookmarks', function(entrada){
	if(entrada){
		bookmarks.object = entrada;
	}
	
	// do not show the parent folder (first child) brcause it is a inexistent folder created by recursive_bookmark_parser.
	recursive_bookmark_parser(ventana, "", bookmarks.getElements(""));
	
	// Complete de widget.
	ventana.appendChild(buttonEdit);
	
	buttonEdit.onclick = function(){}
});

var pos = {
	left: 5,
	top: 55,
	width: 20,
	height: 40
};

API.storage.remoteStorage.get('pos', function(entrada){
	if(entrada){
		pos = entrada;
	}
	
	// Setting size and position
	ventana.setPositionSize(pos.left, pos.top, pos.width, pos.height);
});

// px sizes for the expandable folder functions
var folderSize = 30;
var bookmarSize = 30;


// Setting background color
ventana.style.backgroundColor = "rgba(0, 0, 0, 0.7)";

function recursive_bookmark_parser(element, path, elements){
	var height = 0, i = 0, length = elements.length;
	
	while(i < length){
		switch(typeof elements[i]){
			case "string":
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
			break;
			default:
				// Bookmark
				var a = C("a", ["href", elements[i].uri, "style", "background-color: rgba(255, 255, 255, 0.14);"], elements[i].title ? elements[i].title : elements[i].uri);
				if(elements[i].iconuri){
					a.style.backgroundImage = "url('"+elements[i].iconuri+"')";
				}
				element.appendChild(a);
				height += bookmarSize;			
			break;
		}
		i++;
	}
	
	// Add false bookmark button (button to add folders or bookmarks)
	var a = C("a", ["style", "background-color: rgba(255, 255, 255, 0.14);", "onclick", (function(){
		return function(){
			create_element(path);
		};
	})(path)], "Add Bookmark/Folder");
	element.appendChild(a);
	height += bookmarSize;	
	
	return height;
}

var button_target_bookmark;
var button_target_folder;
var form_target;

// Create a box where you can choose to create a bookmark or a folder in the path "path"
function create_element(path){
	var bookmark_manager_div = API.document.createElement("div").addClass("manager");
	var folder_gui = path === "" ? "the root folder" : "the folder " + path;
	var div_container = C('div', ['class', 'container'],
		C("div", ["class", "txt"], "Creating an element inside "+folder_gui),
		C("div", ["class", "type_selector"],
			button_target_bookmark = C("div", ["onclick", set_target_bookmark, "class", "selected"], "Bookmark"),
			button_target_folder   = C("div", ["onclick", set_target_folder], "Folder")
		),
		form_target = C("div", ["class", "form"]);
	);
	ventana.appendChild(bookmark_manager_div);
	bookmark_manager_div.appendChild(div_container);
}

function set_target_bookmark(){
	change_target_create_element("bookmark");
}
function set_target_folder(){
	change_target_create_element("folder");
}
function change_target_create_element(target){
	switch(target){
		case "bookmark":
			button_target_bookmark.className = "selected";
			button_target_folder.className   = "";
		break;
		case "folder":
			button_target_bookmark.className = "";
			button_target_folder.className   = "selected";
		break;
	}
}

// Function for the config widgetID. It returns the html object to append on the config window
var CONFIG_function = function(functions){
	function realTimeMove(data){
		ventana.setPositionSize(data.left, data.top, data.width, data.height);
	}
	
	functions.positioning(
		{
			"width"   : pos.width,
			"height"  : pos.height,
			"left"    : pos.left,
			"top"     : pos.top,
			"show_bg" : false,
			"realtime": realTimeMove
		},
		function(data){
			if(data){
				pos = {
					left: data.left,
					top: data.top,
					width: data.width,
					height: data.height
				};
				API.storage.remoteStorage.set('pos', pos, function(entrada){
					if(!entrada){
						alert("data not saved");
					}
				});
			}
			else{
				ventana.setPositionSize(pos.left, pos.top, pos.width, pos.height);
			}
		}
	);
	
	
}

// Import css
API.widget.linkMyCSS('css.css').linkExternalCSS("//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css");

//crel2 shortcut
var C = crel2;

// Creating the widget
var ventana = API.widget.create();
ventana.addClass("bookmarks");

var bookmarks_backup;

// Edit bookmarks
var bookmarks = API.bookmarks.createObject();
var buttonEdit = C('div', ['class', 'fa fa-pencil-square button button_add', "onclick", function(){
	ventana.addClass("editing");
	bookmarks_backup = JSON.stringify(bookmarks.getObject());
}]);
// Save bookmarks
var buttonConfirm = C('div', ['class', 'fa fa-check-square button button_confirm', "onclick", function(){
	API.storage.sharedStorage.set('bookmarks', bookmarks.getObject());
	ventana.removeClass("editing");
}]);
// Restore bookmarks
var buttonCancel = C('div', ['class', 'fa fa-ban button button_cancel', "onclick", function(){
	bookmarks.setObject(JSON.parse(bookmarks_backup));
	ventana.removeClass("editing");
	draw_bookmarks();
}]);

API.storage.sharedStorage.get('bookmarks', function(entrada){
	if(entrada){
		bookmarks.setObject(entrada);
	}
	
	draw_bookmarks();
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

var folders_opened = {};

function draw_bookmarks(){
	ventana.innerHTML = "";
	recursive_bookmark_parser(ventana, "", bookmarks.getElements(""));
	ventana.appendChild(buttonEdit);
	ventana.appendChild(buttonConfirm);
	ventana.appendChild(buttonCancel);
}


// Setting background color
ventana.style.backgroundColor = "rgba(0, 0, 0, 0.7)";

function recursive_bookmark_parser(element, path, elements){
	var i = 0, length = elements.length;
	
	while(i < length){
		switch(typeof elements[i]){
			case "string":
				// Folder
				var folder_obj = C('div', ['class', 'folder', 'style', 'height: '+(folders_opened[path] ? 'auto' : '0px')+';']);
				var folderTitle = C('div', ['class','foldername'], elements[i])
				element.appendChild(folderTitle);
				
				recursive_bookmark_parser(folder_obj, path+"/"+elements[i], bookmarks.getElements(path+"/"+elements[i]));
				element.appendChild(folder_obj);
				
				folderTitle.onclick = (function(folder, path){
					return function(){
						if(folder.style.height === "0px"){
							folder.style.height = "auto";
							folders_opened[path] = true;
						}else{
							folder.style.height = "0px";
							delete folders_opened[path];
						}
					}
				})(folder_obj, path);			
			break;
			default:
				// Bookmark
				var a = C("a", ["href", elements[i].uri], elements[i].title ? elements[i].title : elements[i].uri);
				if(elements[i].iconuri){
					a.style.backgroundImage = "url('"+elements[i].iconuri+"')";
				}
				element.appendChild(a);	
			break;
		}
		i++;
	}
	
	// Add false bookmark button (button to add folders or bookmarks)
	var a = C("a", ["class", "addItem", "onclick", (function(){
		return function(){
			create_element(path);
		};
	})(path)], "Add Bookmark/Folder");
	element.appendChild(a);
}

var bookmark_manager_div;
var button_target_bookmark;
var button_target_folder;
var form_target;
var currentPath;

// Create a box where you can choose to create a bookmark or a folder in the path "path"
function create_element(path){
	currentPath = path;
	bookmark_manager_div = API.document.createElement("div").addClass("manager");
	var folder_gui = path === "" ? "the root folder" : "the folder " + path;
	var div_container = C('div', ['class', 'container'],
		C("div", ["class", "txt"], "Creating an element inside "+folder_gui),
		C("div", ["class", "type_selector"],
			button_target_bookmark = C("div", ["onclick", set_target_bookmark, "class", "selected"], "Bookmark"),
			button_target_folder   = C("div", ["onclick", set_target_folder], "Folder")
		),
		form_target = C("div", ["class", "form"]),
		C("div", ["class", "buttons"],
			C("input", ["type", "button", "onclick", ok, "value", "OK"]),
			C("input", ["type", "button", "onclick", cancel, "value", "Cancel"])
		)
	);
	set_target_bookmark();
	ventana.appendChild(bookmark_manager_div);
	bookmark_manager_div.appendChild(div_container);
}

var form_folder_name;
var form_uri;
var form_title;
var currentTarget;

function set_target_bookmark(){
	change_target_create_element("bookmark");
}
function set_target_folder(){
	change_target_create_element("folder");
}
function change_target_create_element(target){
	currentTarget = target;
	switch(target){
		case "bookmark":
			button_target_bookmark.className = "selected";
			button_target_folder.className   = "";
			form_target.innerHTML = "";
			C(form_target,
				form_uri   = C("input", ["type", "text", "placeholder", "Bookmark URL: http://www..."]),
				form_title = C("input", ["type", "text", "placeholder", "Bookmark Title..."])
			);
		break;
		case "folder":
			button_target_bookmark.className = "";
			button_target_folder.className   = "selected";
			form_target.innerHTML = "";
			C(form_target,
				form_folder_name = C("input", ["type", "text", "placeholder", "Folder Name..."])
			);
		break;
	}
}

function ok(){
	switch(currentTarget){
		case "bookmark":
			bookmarks.addBookmark(currentPath, form_uri.value, form_title.value/*, icon_uri*/);
		break;
		case "folder":
			bookmarks.addFolder(currentPath, form_folder_name.value);
		break;
	}
	draw_bookmarks();
	bookmark_manager_div.remove();
}
function cancel(){
	bookmark_manager_div.remove();
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

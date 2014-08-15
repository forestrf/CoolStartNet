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
		var deleteButton = C('div', ['class', "delete fa fa-trash-o"]);
		var editButton = C('div', ['class', "edit fa fa-pencil"]);
		switch(typeof elements[i]){
			case "string":
				// Folder
				var folderTitle = C('div', ['class','foldername'], elements[i]);
				var folder_obj = C('div', ['class', 'folder', 'style', 'height: '+(folders_opened[path+"/"+elements[i]] ? 'auto' : '0px')+';']);
				element.appendChild(folderTitle);
				element.appendChild(deleteButton);
				element.appendChild(editButton);
				
				recursive_bookmark_parser(folder_obj, path+"/"+elements[i], bookmarks.getElements(path+"/"+elements[i]));
				element.appendChild(folder_obj);
				
				folderTitle.onclick = (function(folder, path){
					return function(){
						if(folder.style.height === "0px"){
							folder.style.height = "auto";
							folders_opened[path] = true;
						}else{
							folder.style.height = "0px";
							folders_opened[path] = false;
						}
					}
				})(folder_obj, path+"/"+elements[i]);
				
				deleteButton.onclick = (function(path, name){
					return function(){
						bookmarks.removeFolder(path, name);
						draw_bookmarks();
					}
				})(path, elements[i]);
				
				editButton.onclick = (function(path, name){
					return function(event){
						create_element(path, 'folder', false, 'editing a folder inside ', function(){
							bookmarks.editFolder(path, name, form_folder_name.value);
							draw_bookmarks();
						});
						form_folder_name.value = name;
					}
				})(path, elements[i]);
			break;
			default:
				// Bookmark
				var a = C("a", ["href", elements[i].uri, "style", "background-image: url(http://g.etfv.co/"+encodeURI(elements[i].uri)+")"], elements[i].title ? elements[i].title : elements[i].uri);
				
				element.appendChild(a);
				element.appendChild(deleteButton);
				element.appendChild(editButton);
				
				deleteButton.onclick = (function(path, i){
					return function(event){
						bookmarks.removeBookmark(path, i);
						draw_bookmarks();
					}
				})(path, i);
				
				editButton.onclick = (function(path, i){
					return function(event){
						create_element(path, 'bookmark', false, 'editing a bookmark inside ', function(){
							bookmarks.editBookmark(path, i, form_uri.value, form_title.value);
							draw_bookmarks();
						});
						form_uri.value   = elements[i].uri;
						form_title.value = elements[i].title;
					}
				})(path, i);
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
var button_target_bookmark, button_target_folder;
var form_target;

var currentPath;
var currentTarget;

var form_folder_name;
var form_uri;
var form_title;

// Create a box where you can choose to create a bookmark or a folder in the path "path"
function create_element(path, selected, show_selector, message, ok_callback){
	if(selected      === undefined) selected = "bookmark";
	if(show_selector === undefined) show_selector = true;
	if(message       === undefined) message = "Creating an element inside ";
	if(ok_callback   === undefined) ok_callback = ok;
	
	currentPath = path;
	var folder_gui = path === "" ? "the root folder" : "the folder " + path;
	
	C(ventana, 
		C(bookmark_manager_div = API.document.createElement("div").addClass("manager"),
			C('div', ['class', 'container'],
				C("div", ["class", "txt"], message + folder_gui),
				C("div", ["class", "type_selector", "style", show_selector ? "" : "display:none;"],
					button_target_bookmark = C("div", ["onclick", function(){change_target_create_element("bookmark")}, "class", selected === 'bookmark' ? "selected" : ''], "Bookmark"),
					button_target_folder   = C("div", ["onclick", function(){change_target_create_element("folder");},  "class", selected === 'folder'   ? "selected" : ''], "Folder")
				),
				form_target = C("div", ["class", "form"]),
				C("div", ["class", "buttons"],
					C("input", ["type", "button", "onclick", ok_callback, "value", "OK"]),
					C("input", ["type", "button", "onclick", cancel, "value", "Cancel"])
				)
			)
		)
	);
	
	change_target_create_element(selected);
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
			if(form_uri.value.indexOf("http") !== 0){
				form_uri.value = "http://"+form_uri.value;
			}
			bookmarks.addBookmark(currentPath, form_uri.value, form_title.value);
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
	
	return C('div',
		C('button', ['onclick', setPosition], 'Set position')
	);
	
	
	
	function realTimeMove(data){
		ventana.setPositionSize(data.left, data.top, data.width, data.height);
	}
	
	function setPosition(){
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
	
	
}

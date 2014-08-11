/*
API.Storage.sharedStorage.get('bookmarks', function(entrada){
	if(entrada){
		console.log(entrada);
	}
	else{
		log('GLOBAL GET Test There is not a saved variable with that name.');
	}
});

API.Storage.sharedStorage.set('bookmarks', ["a","b",{"c":"d"}], function(entrada){
	if(entrada){
		console.log(entrada);
	}
	else{
		log('GLOBAL GET Test There is not a saved variable with that name.');
	}
});
*/

// Import css
API.Widget.linkMyCSS('css.css');

//crel2 shortcut
var C = crel2;

// Creating the widget
var ventana = API.Widget.create();
ventana.addClass("bookmarks");

// Setting size and position
var left = 5;
var top = 65;
var width = 20;
var height = 30;
ventana.setPositionSize(left, top, width, height);

// Setting background color
ventana.style.backgroundColor = "rgba(0, 0, 0, 0.7);";



var bookmarks = API.Bookmarks.createObject()
.addBookmark("/", "http://1")
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

console.log(bookmarks);


// do not show the parent folder (first child) brcause it is a inexistent folder created by recursive_bookmark_parser.
var hierarchi = recursive_bookmark_parser("", bookmarks.getElements(""));
while(hierarchi.childNodes.length){
	ventana.appendChild(hierarchi.childNodes[0]);
}



function recursive_bookmark_parser(path, elements, folderName){
	var folder_obj = C('div', ['class', 'folder']);
	if(folderName !== undefined){
		folder_obj.appendChild(C('span', ['class','foldername'], folderName));
	}
	
	var i = 0;
	while(i < elements.length){
		if(typeof elements[i] === "string"){
			// Folder
			folder_obj.appendChild(
				recursive_bookmark_parser(path+"/"+elements[i], bookmarks.getElements(path+"/"+elements[i]), elements[i])
			);
		}
		else if(elements[i].type === "bookmark"){
			// Bookmark
			var a = C("a", ["href", elements[i].uri, "style", "background-color: rgba(255, 255, 255, 0.14);"], elements[i].title ? elements[i].title : elements[i].uri);
			if(elements[i].iconuri){
				a.style.backgroundImage = "url('"+elements[i].iconuri+"')";
			}
			folder_obj.appendChild(a);
		}
		i++;
	}
	return folder_obj;
}
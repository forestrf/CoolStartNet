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

var C = crel2;

var ventana = API.Widget.create();
ventana.addClass("bookmarks");



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


ventana.appendChild(recursive_bookmark_parser("", bookmarks.getElements("")));



function recursive_bookmark_parser(path, elements){
	var folder_obj = C('div');
	for(var i = 0; i < elements.length; i++){
		if(typeof elements[i] === "string"){
			folder_obj.appendChild(
				recursive_bookmark_parser(path+"/"+elements[i], bookmarks.getElements(path+"/"+elements[i]))
			);
		}
		else if(elements[i].type === "bookmark"){
			var a = C("a", ["href", elements[i].uri], elements[i].title ? elements[i].title : elements[i].uri);
			if(elements[i].iconuri){
				a.setAttribute("style", "background-image: url('"+elements[i].iconuri+"');");
			}
			folder_obj.appendChild(a);
			/*<div class="tags_display">
				{LINK_TAGS}
					<div class="tag_nombre_{elem}"></div>
				{/LINK_TAGS}
			</div>*/
		}
	}
	return folder_obj;
}
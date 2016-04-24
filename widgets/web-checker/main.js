// Import css
API.widget.linkMyCSS('css.css');

//crel2 shortcut
var C = crel2;

// Creating the widget
var ventana = API.widget.create();
ventana.addClass("webchecker");

var urls;
C(ventana, 
	C("div", ["class", "buttons"],
		C("button", ["onclick", agregaURL], "Add Web"),
		C("button", ["onclick", comprobarWebs], "Check webs..."),
		C("button", ["onclick", setProxy], "Proxy")
	),
	urls = C("div", ["class", "content"])
);

var websToCheck = [];
var proxy = 'http://127.0.0.1';

API.storage.remoteStorage.get('websToCheck', function(data){
	if(data && data.length > 0){
		websToCheck = data;
		for (var i = 0; i < websToCheck.length; i++) {
			websToCheck[i].matched = "";
			websToCheck[i].html = null;
		}
		Draw();
	}
}).get('proxy', function(data){
	if(data) proxy = data;
});

function Draw() {
	urls.innerHTML = '';
	for (var i = 0; i < websToCheck.length; i++) {
		if (websToCheck[i].web === null) {
			websToCheck.splice(i, 1);
			save();
			i--;
		} else {
			C(urls, ReDrawElement(websToCheck[i]));
		}
	}
}

function ReDrawElement(elem) {
	if (elem.html === null) {
		elem.html = C("div", ["class", elem.matched]);
		API.document.wrapElement(elem.html);
	}
	elem.html.innerHTML = '';
	C(elem.html, ["class", elem.matched], 
		C("button", ["onclick", function() {
			edit(elem);
		}], "Edit"),
		C("button", ["onclick", function() {
			var sure = prompt("Are you sure? (yes/no)", "no");
			if (sure === "yes") {
				var i = websToCheck.indexOf(elem);
				websToCheck.splice(i, 1);
				save();
				Draw();
			}
		}], "Remove"),
		C("span", "/" + elem.regex + "/"),
		C("a", ["href", elem.web, "target", "_blank"], elem.web)
	);
	return elem.html;
}

function agregaURL() {
	websToCheck = websToCheck.concat({
		web: "http://...",
		regex: "",
		wantToMatch: true,
		matched: "",
		html: null
	});
	edit(websToCheck[websToCheck.length - 1]);
	save();
	Draw();
}

function edit(elem) {
	var web         = prompt("Web.............................................................................................................................................................................", elem.web);
	if (web)         elem.web = web;
	var regex       = prompt("Regex to match..................................................................................................................................................................", elem.regex);
	if (regex)       elem.regex = regex;
	var wantToMatch = prompt("Wants the regex to match? (y/yes/n/no)..........................................................................................................................................", elem.wantToMatch ? "yes" : "no");
	if (wantToMatch) elem.wantToMatch = ["yes","y"].indexOf(wantToMatch.toLocaleLowerCase()) != -1;
	elem.matched = "";
	ReDrawElement(elem);
	save();
}

function comprobarWebs() {
	for (var i = 0; i < websToCheck.length; i++) {
		check(websToCheck[i]);
	}
	save();
}

function check(webElement) {
	API.xhr(proxy + encodeURIComponent(webElement.web), null, function(data) {
		var check = RegExp(webElement.regex).test(data);
		var ok = (check && webElement.wantToMatch) || (!check && !webElement.wantToMatch);
		webElement.matched = ok ? "matched" : "unmatched";
		webElement.html.addClass(webElement.matched).removeClass(!ok ? "matched" : "unmatched");
	});
}

function save() {
	API.storage.remoteStorage.set('websToCheck', GenerateSave());
}

function setProxy() {
	var p = prompt("Enter proxy to circumvent CORS", proxy);
	if (p) {
		proxy = p;
		API.storage.remoteStorage.set('proxy', proxy);
	}
}

function GenerateSave() {
	s = [];
	for (var i = 0; i < websToCheck.length; i++) {
		s = s.concat({
			web: websToCheck[i].web,
			regex: websToCheck[i].regex,
			wantToMatch: websToCheck[i].wantToMatch,
		});
	}
	return s;
}



////////////////////////////
// CONFIGS
////////////////////////////

var pos = {
	left: 5,
	top: 55,
	width: 20,
	height: 40
};

API.storage.remoteStorage.get('pos', function(entrada){
	if(entrada) pos = entrada;
	
	// Setting size and position
	ventana.setPositionSize(pos.left, pos.top, pos.width, pos.height);
});

// Setting background color
ventana.style.backgroundColor = "rgba(0, 0, 0, 0.7)";

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
	
	
};

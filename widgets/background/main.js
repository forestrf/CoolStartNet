// Import css
var link = document.createElement("link");
link.setAttribute("rel", "stylesheet");
link.setAttribute("type", "text/css");
link.setAttribute("href", API.url(widgetID,'bgcss.css'));
document.getElementsByTagName("head")[0].appendChild(link);

// Make the div container for the background and style it.
var fondoDiv = document.createElement('div');
var fondoDiv2 = document.createElement('div');

// Style the background div
fondoDiv.className = 'background_widget_bg1';

// Style of the inner background div (used for transitioning backgrounds)
fondoDiv2.className = 'background_widget_bg2 background_widget_transparent';

// Append the divs
document.body.appendChild(fondoDiv);
fondoDiv.appendChild(fondoDiv2);



var relations = {
	'1':'backgroundImage',
	'2':'backgroundColor',
	'3':'backgroundPosition',
	'4':'backgroundRepeat',
	'5':'backgroundSize',
	'a':'left left',
	'b':'left center',
	'c':'left right',
	'd':'center left',
	'e':'center center',
	'f':'center right',
	'g':'right left',
	'h':'right center',
	'i':'right right',
	'j':'repeat',
	'k':'repeat-x',
	'l':'repeat-y',
	'm':'no-repeat',
	'n':'cover',
	'o':'contain',
	'p':'auto'
};




var backgrounds = [];
backgrounds.push({
	'1':'https://dl.dropboxusercontent.com/u/1630604/imagenes%20b/wallpapers/1391803994183.jpg',
	'2':'#000000',
	'3':'e',
	'4':'m',
	'5':'n'
});
backgrounds.push({
	'1':'https://dl.dropboxusercontent.com/u/1630604/imagenes%20b/wallpapers/1391407706614.jpg',
	'2':'#000000',
	'3':'e',
	'4':'m',
	'5':'n'
});
backgrounds.push({
	'1':'https://dl.dropboxusercontent.com/u/1630604/imagenes%20b/wallpapers/1390952811479.jpg',
	'2':'#000000',
	'3':'e',
	'4':'m',
	'5':'n'
});

var transitionTime = 3000; //ms
var delayBackground = 60000; //ms
var next = parseInt(Math.random() *backgrounds.length);


setBackground(fondoDiv, backgrounds[next]);

if(++next > backgrounds.length -1)
	next = 0;

setBackground(fondoDiv2, backgrounds[next]);


// Interval to change backgrounds over time
setInterval(function(){
	fondoDiv2.className = fondoDiv2.className.split("background_widget_transparent").join("");
	
	setTimeout(function(){
		setBackground(fondoDiv, backgrounds[next]);
		
		++next;
		if(next > backgrounds.length -1)
			next = 0;
		
		//Preload the next background
		fondoDiv2.className += "background_widget_transparent";
		setBackground(fondoDiv2, backgrounds[next]);
		
		
	}, transitionTime);
}, delayBackground);


// Change the style of a div to make it a background with the options of the background variable
function setBackground(div, background){
	div.style[relations[1]] = 'url("'+background[1]+'")';
	div.style[relations[2]] = background[2];
	div.style[relations[3]] = relations[background[3]];
	div.style[relations[4]] = relations[background[4]];
	div.style[relations[5]] = relations[background[5]];
}





// Function for the config widgetID. It returns the html object to append on the config window
var CONFIG_function = function(){
	
	// Each row of the table will be a td with an input and another td with a description and the save button
	var table = document.createElement('table');
	var tbody = document.createElement('tbody');
	table.appendChild(tbody);
	
	
	// Time between transitions
	var tr = document.createElement('tr');
	var td = document.createElement('td');
	var inputDelayBackground = document.createElement('input');
	inputDelayBackground.value = delayBackground/1000; // ms to s
	td.appendChild(inputDelayBackground);
	tr.appendChild(td);
	
	td = document.createElement('td');
	td.innerHTML = 'Time between transitions (in seconds)';
	tr.appendChild(td);
	tbody.appendChild(tr);
	
	
	// Time transitioning (in seconds)
	tr = document.createElement('tr');
	td = document.createElement('td');
	var inputTransitionTime = document.createElement('input');
	inputTransitionTime.value = transitionTime/1000; // ms to s
	td.appendChild(inputTransitionTime);
	tr.appendChild(td);
	
	td = document.createElement('td');
	td.innerHTML = 'Time transitioning (in seconds)';
	tr.appendChild(td);
	tbody.appendChild(tr);
	
	
	
	
	
	
	var inputDelayBackgroundTime = document.createElement('input');
	inputDelayBackgroundTime.value = transitionTime;
	
	
	
	
	
	return table;
}

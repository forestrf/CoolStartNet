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
	0:'backgroundImage',
	1:'backgroundColor',
	2:'backgroundPosition',
	3:'backgroundRepeat',
	4:'backgroundSize',
	'a':'left top',
	'b':'left center',
	'c':'left bottom',
	'd':'center top',
	'e':'center center',
	'f':'center bottom',
	'g':'right top',
	'h':'right center',
	'i':'right bottom',
	'j':'repeat',
	'k':'repeat-x',
	'l':'repeat-y',
	'm':'no-repeat',
	'n':'cover',
	'o':'contain',
	'p':'auto'
};




var backgrounds = [];
backgrounds.push([
	'http://static.freepik.com/foto-gratis/poligono-de-fondo_72818.jpg',
	'#000000',
	'e',
	'm',
	'n'
]);
backgrounds.push([
	'http://img.wallpaperstock.net:81/wallpapers/thumbs1/36523wide.jpg',
	'#000000',
	'e',
	'm',
	'n'
]);

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
	div.style[relations[0]] = 'url("'+background[0]+'")';
	div.style[relations[1]] = background[1];
	for(var i = 2; i < 5; ++i){
		div.style[relations[i]] = relations[background[i]];
	}
}





// Function for the config widgetID. It returns the html object to append on the config window
var CONFIG_function = function(){
	var div = document.createElement('div');
	
	// Each row of the table will be a td with an input and another td with a description and the save button
	var table = document.createElement('table');
	
	
	// Time between transitions
	var tr = document.createElement('tr');
	var td = document.createElement('td');
	var inputDelayBackground = document.createElement('input');
	inputDelayBackground.type = 'text';
	inputDelayBackground.value = delayBackground/1000; // ms to s
	td.appendChild(inputDelayBackground);
	tr.appendChild(td);
	
	td = document.createElement('td');
	td.innerHTML = 'Time between transitions (in seconds)';
	tr.appendChild(td);
	table.appendChild(tr);
	
	
	// Time transitioning (in seconds)
	tr = document.createElement('tr');
	td = document.createElement('td');
	var inputTransitionTime = document.createElement('input');
	inputTransitionTime.type = 'text';
	inputTransitionTime.value = transitionTime/1000; // ms to s
	td.appendChild(inputTransitionTime);
	tr.appendChild(td);
	
	td = document.createElement('td');
	td.innerHTML = 'Time transitioning (in seconds)';
	tr.appendChild(td);
	table.appendChild(tr);
	
	table.appendChild(document.createElement('br'));
	table.appendChild(document.createElement('br'));
	
	div.appendChild(table);
	
	
	
	
	
	table = document.createElement('table');
	
	
	
	// Background list
	for(var i in backgrounds){
		table.appendChild(createBGTableElement(backgrounds[i]));
	}
	
	div.appendChild(table);
	
	
	
	
	
	// Returns a tr for the table of backgrounds
	function createBGTableElement(background){
		var trBody = document.createElement('tr');
		var td = document.createElement('td');
		
		// First TD is for the image, second TD for the inputs and the text (that are in another table)
		var previewPic = document.createElement('img');
		previewPic.style.cssFloat = 'right';
		previewPic.style.height   = '10em';
		previewPic.style.maxWidth = '25em';
		if(background){
			previewPic.src = background[0];
		}
		td.appendChild(previewPic);
		trBody.appendChild(td);
		
		td = document.createElement('td');
		trBody.appendChild(td);
		
		// inputs and text
		var table = document.createElement('table');
		
		td.appendChild(table);
		
		
		
		// URL input
		var tr = document.createElement('tr');
		td = document.createElement('td');
		tr.appendChild(td);
		var input = document.createElement('input');
		input.type = 'text';
		input.value = background ? background[0] : '';
		input.placeholder = 'http://www.domain.com/image.jpg';
		input.onchange = input.onkeyup = function(){
			previewPic.src = this.value;
		}
		td.appendChild(input);
		
		td = document.createElement('td');
		td.innerHTML = 'URL of the background';
		tr.appendChild(td);
		table.appendChild(tr);
		
		
		
		// COLOR input
		tr = document.createElement('tr');
		td = document.createElement('td');
		tr.appendChild(td);
		input = document.createElement('input');
		input.type = 'text';
		input.value = background ? background[1] : '#000000';
		input.placeholder = '#000000';
		td.appendChild(input);
		
		td = document.createElement('td');
		td.innerHTML = 'Color to fill the background outsides';
		tr.appendChild(td);
		table.appendChild(tr);
		
		
		
		// POSITION input
		tr = document.createElement('tr');
		td = document.createElement('td');
		tr.appendChild(td);
		input = document.createElement('select');
		input.innerHTML = '<option value="e">center center</option><option value="d">center top</option><option value="f">center bottom</option><option value="a">left top</option><option value="b">left center</option><option value="c">left bottom</option><option value="g">right top</option><option value="h">right center</option><option value="i">right bottom</option>';
		td.appendChild(input);
		
		td = document.createElement('td');
		td.innerHTML = 'Position of the background';
		tr.appendChild(td);
		table.appendChild(tr);
		
		
		
		// REPEAT input
		tr = document.createElement('tr');
		td = document.createElement('td');
		tr.appendChild(td);
		input = document.createElement('select');
		input.innerHTML = '<option value="j">repeat</option><option value="k">repeat-x</option><option value="l">repeat-y</option><option value="m">no-repeat</option>';
		td.appendChild(input);
		
		td = document.createElement('td');
		td.innerHTML = 'Background repeating to fill the window';
		tr.appendChild(td);
		table.appendChild(tr);
		
		
		
		// SIZE input
		tr = document.createElement('tr');
		td = document.createElement('td');
		tr.appendChild(td);
		input = document.createElement('select');
		input.innerHTML = '<option value="n">cover</option><option value="o">contain</option><option value="p">auto</option>';
		td.appendChild(input);
		
		td = document.createElement('td');
		td.innerHTML = 'How the background scales to fill the window';
		tr.appendChild(td);
		table.appendChild(tr);
		
		
		
		// REMOVE input
		tr = document.createElement('tr');
		td = document.createElement('td');
		tr.appendChild(td);
		input = document.createElement('button');
		input.innerHTML = "Remove";
		td.appendChild(input);
		table.appendChild(tr);
		
		
		
		
		
		
		
		
		return trBody;
	}
		
	return div;
}

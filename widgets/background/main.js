// Import css
API.widget.linkMyCSS('bgcss.css');

// Make the div container for the background and style it.
var fondoDiv = API.widget.create();
var fondoDiv2 = API.widget.create();

// Style the background div
fondoDiv.addClass('background_widget_bg1');

// Style of the inner background div (used for transitioning backgrounds)
fondoDiv2.addClass('background_widget_bg2');
fondoDiv2.style.opacity = 0;



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


// Default variables
var backgrounds = [];
var transitionTime = 3000; //ms
var delayBackground = 60000; //ms



API.storage.sharedStorage.get('background_delay', function(entrada){
	if(entrada){
		delayBackground = entrada;
	}
}).get('background_transition_time', function(entrada){
	if(entrada){
		transitionTime = entrada;
		fondoDiv2.style.transition = 'opacity ' + transitionTime/1000 + 's ease';
	}
}).get('background_images', function(entrada){
	if(entrada){
		backgrounds = entrada;
		launch();
	}
});





var launched = false;
var next = 0;

var intervalPlaceHolder;

function launch(force){
	if(force === true){
		if(launched){
			clearInterval(intervalPlaceHolder);
		}
	}
	else if(launched){
		return;
	}
	launched = true;
	
	// next === backgrounds.length => nearly impossible
	var next = Math.floor(Math.random() *backgrounds.length);


	setBackground(fondoDiv, backgrounds[next]);

	if(++next > backgrounds.length -1){
		next = 0;
	}

	setBackground(fondoDiv2, backgrounds[next]);


	// Interval to change backgrounds over time
	intervalPlaceHolder = setInterval(function(){
		if(++next > backgrounds.length -1){
			next = 0;
		}
		if(fondoDiv2.style.opacity === '1'){
			fondoDiv2.style.opacity = 0;
			var nextDiv = fondoDiv2;
		}
		else{
			fondoDiv2.style.opacity = 1;
			var nextDiv = fondoDiv;
		}
		
		//Preload the next background
		setTimeout(function(){
			setBackground(nextDiv, backgrounds[next]);
		}, transitionTime);
		
	}, - -delayBackground - -transitionTime);
}



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
	
	// Variables
	
	// Make a copy of the backgrounds and use it to change configurations without touch the current background setup
	var backgrounds_copy = backgrounds.slice(0),
		inputDelayBackground,
		inputTransitionTime,
		tableBackgrounds,
		i = 0,
		C = crel2;
	
	
	
	var div = C('div',
		// Each row of the table will be a td with an input and another td with a description and the save button
		C('table',
			C('tr',
				C('td',
					// Time between transitions
					inputDelayBackground = C('input', ['type', 'text', 'value', delayBackground/1000]) // ms to s
				),
				C('td', 'Time between transitions (in seconds)')
			),
			C('tr',
				C('td',
					// Time transitioning (in seconds)
					inputTransitionTime = C('input', ['type', 'text', 'value', transitionTime/1000]) // ms to s
				),
				C('td', 'Time transitioning (in seconds)')
			)
		),
		C('br'),
		C('br'),
		tableBackgrounds = C('table'),
		C('br'),
		C('br'),
		C('button', ['onclick', addBackground], 'Add background'),
		C('br'),
		C('br'),
		C('br'),
		C('button', ['onclick', saveBackgrounds], 'Save changes')
	);
	
	// Background list
	while(i < backgrounds_copy.length){
		tableBackgrounds.appendChild(createBGTableElement(backgrounds_copy[i++]));
	}
	
	return div;
	
	
	
	function addBackground(){
		// This is the default background
		backgrounds_copy.push(['','#000000','e','m','n']);
		tableBackgrounds.appendChild(createBGTableElement(backgrounds_copy[backgrounds_copy.length-1]));
	}
	
	function removeBackground(background){
		// Remove the object from the array and delete the tr of the table that corresponds to the background
		var index = backgrounds_copy.indexOf(background);
		backgrounds_copy.splice(index, 1);
		tableBackgrounds.removeChild(childNodes[index]);
	}
	
	function saveBackgrounds(){
		// Change the local variables to the new ones
		backgrounds = backgrounds_copy;
		delayBackground = inputDelayBackground.value *1000; // s to ms
		transitionTime = inputTransitionTime.value *1000; // s to ms
		
		// Save the variables using the API
		API.storage.sharedStorage.set('background_images', backgrounds).
		set('background_delay', delayBackground).
		set('background_transition_time', transitionTime);

		/*
		API.call( [...] , function(entrada){
			if(entrada['background_images'] && entrada['background_delay'] && entrada['background_transition_time']){
				alert('Saved');
			}
			else{
				alert('NOT saved.');
			}
		});*/
		
		launch(true);
	}
	
	
	// Returns a tr for the table of backgrounds
	function createBGTableElement(background){
		var previewPic;
		
		return C('tr',
			C('td',
				// First TD is for the image, second TD for the inputs and the text (that are in another table)
				previewPic = C('img', [
					'class', 'background_widget_preview_pic',
					'src', background ? background[0] : ''
				])
			),
			C('td',
				// inputs and text
				C('table',
					// URL input
					C('tr',
						C('td',
							C('input', [
								'type', 'text',
								'placeholder', 'http://www.domain.com/image.jpg',
								'value', background ? background[0] : '',
								'onchange', function(){previewPic.src = background[0] = this.value;},
								'onkeyup', function(){previewPic.src = background[0] = this.value;}
							])
						),
						C('td', 'URL of the background')
					),
					// COLOR input
					C('tr',
						C('td',
							C('input', [
								'type', 'text',
								'placeholder', '#000000',
								'value', background ? background[1] : '#000000',
								'onchange', function(){background[1] = this.value;}
							])
						),
						C('td', 'Hexadecimal color to fill the background outsides')
					),
					// POSITION input
					C('tr',
						C('td',
							C('select', ['onchange', function(){background[2] = this.value;}],
								C('option', ['value', 'e'], 'center center'),
								C('option', ['value', 'd'], 'center top'),
								C('option', ['value', 'f'], 'center bottom'),
								C('option', ['value', 'a'], 'left top'),
								C('option', ['value', 'b'], 'left center'),
								C('option', ['value', 'c'], 'left bottom'),
								C('option', ['value', 'g'], 'right top'),
								C('option', ['value', 'h'], 'right center'),
								C('option', ['value', 'i'], 'right bottom')
							)
						),
						C('td', 'Position of the background')
					),
					// REPEAT input
					C('tr',
						C('td',
							C('select', ['onchange', function(){background[3] = this.value;}],
								C('option', ['value', 'j'], 'Repeat'),
								C('option', ['value', 'k'], 'Repeat horizontally'),
								C('option', ['value', 'l'], 'Repeat vertically'),
								C('option', ['value', 'm'], 'No repeat')
							)
						),
						C('td', 'Background repeating to fill the window')
					),
					// SIZE input
					C('tr',
						C('td',
							C('select', ['onchange', function(){background[4] = this.value;}],
								C('option', ['value', 'n'], 'Fit Best and maintain Aspect Ratio (Clip Edges)'),
								C('option', ['value', 'o'], 'Fit Best and maintain Aspect Ratio (No Clipping)'),
								C('option', ['value', 'p'], 'Original size')
							)
						),
						C('td', 'How the background scales to fill the window')
					),
					// REMOVE input
					C('tr',
						C('td',
							C('button', ['onclick', function(){removeBackground(background);}], 'Remove')
						)
					)
				)
			)
		);
		
		
	}
}

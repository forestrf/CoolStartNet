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



// Default variables
var backgrounds = [];
var transitionTime = 3000; //ms
var delayBackground = 60000; //ms



API.storage.remoteStorage.get('delay', function(entrada){
	if(entrada){
		delayBackground = entrada;
	}
}).get('transition_time', function(entrada){
	if(entrada){
		transitionTime = entrada;
		fondoDiv2.style.transition = 'opacity ' + transitionTime/1000 + 's ease';
	}
}).get('backgrounds', function(entrada){
	if(entrada){
		backgrounds = entrada;
		launch();
	} else {
		API.dropbox.getPathContents('/wallpapers', function(data){
			if(data){
				backgrounds = data['files'];
				launch();
				API.storage.remoteStorage.set('backgrounds', backgrounds);
			}
		});
	}
});



var next = 0;
var interval;

function launch(){
	
	// next === backgrounds.length => nearly impossible
	var next = Math.floor(Math.random() *backgrounds.length);
	setBackground(fondoDiv, backgrounds[next]);
	
	next = Math.floor(Math.random() *backgrounds.length);
	setBackground(fondoDiv2, backgrounds[next]);


	// Interval to change backgrounds over time
	interval = setInterval(function(){
		next = Math.floor(Math.random() *backgrounds.length);
		
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
	background = API.dropbox.getFileURI(background);
	div.style.backgroundImage = 'url("'+background+'")';
	div.style.backgroundColor = '#000';
	div.style.backgroundPosition = 'center center';
	div.style.backgroundRepeat = 'no-repeat';
	div.style.backgroundSize = 'cover';
}





// Function for the config widgetID. It returns the html object to append on the config window
var CONFIG_function = function(){
	
	// Variables
	
	// Make a copy of the backgrounds and use it to change configurations without touch the current background setup
	var inputDelayBackground,
		inputTransitionTime,
		C = crel2;
	
	
	
	var div = C('div',"Put your wallpapers inside a folder called wallpapers on your dropbox application folder",
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
			),
			C('tr',
				C('td',
					// Refresh cache
					C('button', ['onclick', function(){
						API.dropbox.getPathContents('/wallpapers', function(data){
							if(data){
								backgrounds = data['files'];
								clearInterval(interval);
								launch();
								API.storage.remoteStorage.set('backgrounds', backgrounds, function(status){
									alert(status ? 'Cache refreshed!' : 'Cache refresh failed!');
								});
							}
						});
					}], 'Refresh list cache')
				),
				C('td', 'Refresh the wallpaper cached list from dropbox')
			)
		),
		C('br'),
		C('br'),
		C('br'),
		C('button', ['onclick', saveBackgrounds], 'Save changes')
	);
	
	return div;
	
	function saveBackgrounds(){
		// Change the local variables to the new ones
		delayBackground = inputDelayBackground.value *1000; // s to ms
		transitionTime = inputTransitionTime.value *1000; // s to ms
		
		// Save the variables using the API
		API.storage.remoteStorage.set('delay', delayBackground).
		set('transition_time', transitionTime, function(status){
			alert(status ? 'Saved!' : 'Saved failed!');
		});
	}
}

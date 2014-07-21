// CREATE AND APPEND GEAR TO THE BODY

// Import css (Github icons pack)
var link = document.createElement("link");
link.setAttribute("rel", "stylesheet");
link.setAttribute("type", "text/css");
link.setAttribute("href", "//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css");
document.getElementsByTagName("head")[0].appendChild(link);

// Import css (css of the widget)
link = document.createElement("link");
link.setAttribute("rel", "stylesheet");
link.setAttribute("type", "text/css");
link.setAttribute("href", API.url(widgetID,'css.css'));
document.getElementsByTagName("head")[0].appendChild(link);

// Make the div container for the gear button
var gearDiv = document.createElement('div');
gearDiv.className = 'config_buttongear';
gearDiv.innerHTML = '<i class="fa fa-cog"></i>';

// Append the gear to the body
document.body.appendChild(gearDiv);





// CREATE AND APPEND CONTENT CONFIGURATION DIV

// Make the div container for the config window
var contentDiv = document.createElement('div');
contentDiv.className = 'config_contentdiv';

// Append the config window to the body
document.body.appendChild(contentDiv);

// Make the div container for the widgets in the config window and append the container for the widgets in the config window
var contentwidgetsDiv = document.createElement('div');
contentwidgetsDiv.className = 'contentwidgets';
contentDiv.appendChild(contentwidgetsDiv);

// Make the div container for the result of the configuration function of each widget (share the div) and append the container for the widgets in the config window
var configwidgetDiv = document.createElement('div');
configwidgetDiv.className = 'configwidget';
configwidgetDiv.style.display = 'none';
contentDiv.appendChild(configwidgetDiv);

// Placeholder for the back button variable
var backbutton;

// Create the close button for the config window
var closebutton = document.createElement('i');
closebutton.className = 'fa fa-times closebutton';
contentDiv.appendChild(closebutton);
closebutton.onclick = function(){
	// Reset content
	contentwidgetsDiv.innerHTML = '';
	configwidgetDiv.innerHTML = '';
	configwidgetDiv.style.display = 'none';
	if(backbutton){
		backbutton.remove();
	}
	// Hide window
	contentDiv.className = contentDiv.className.split('visible').join('').trim();
};



// Function that cleans the config window and shows redimensionable and movible rectangle.
// Calls callback with the position and size of the rectangle once the user is done or false if the user canceled the operation.
// Used by the widgets that can and wants to change their position and size.
// Parameters:
/*
{
	"width"  : number, // %
	"height" : number, // %
	"left"   : number, // %
	"top"    : number  // %
}
*/
function generate_position_rect(parameters, callback){
	// CREATE AND APPEND CONTENT CONFIGURATION DIV

	// Make the div container for the rect
	var contentDivRect = document.createElement('div');
	contentDivRect.className = 'config_contentDivRect';
	contentDivRect.style.backgroundImage = 'url(' + API.url(widgetID,'grid.png') + ')';
	document.body.appendChild(contentDivRect);
	
	// Hide contentDiv
	contentDiv.style.display = 'none';
	
	// Make the Info div
	var infoDiv = document.createElement('div');
	infoDiv.className = 'config_rectInfo';
	contentDivRect.appendChild(infoDiv);
	
	// Make the div container for the buttons
	var buttonsDiv = document.createElement('div');
	buttonsDiv.className = 'config_rectInfo_buttons';
	infoDiv.appendChild(buttonsDiv);
	
	// "Ok" and "Cancel" buttons
	var buttonOK = document.createElement('button');
	buttonOK.innerHTML = 'Ok';
	buttonsDiv.appendChild(buttonOK);
	var buttonCANCEL = document.createElement('button');
	buttonCANCEL.innerHTML = 'Cancel';
	buttonsDiv.appendChild(buttonCANCEL);
	
	// Make the div container for the inputs
	var inputsDiv = document.createElement('div');
	inputsDiv.className = 'config_rectInfo_inputs';
	infoDiv.appendChild(inputsDiv);
	
	// Generate the interior of the Info div
	var inputs = []; // W, H, L ,R
	var inputs_relation = {0:"width",1:"height",2:"left",3:"top"};
	for(var i = 0; i < 4; ++i){
		inputs[i] = document.createElement("input");
		inputs[i].onkeyup = inputs[i].onblur = (function(i, dimension){
			return function(){
				rectPosition.style[dimension] = inputs[i].value +'%';
			}
		})(i, inputs_relation[i]);
	}
	
	inputsDiv.appendChild(document.createTextNode("Width "));
		inputsDiv.appendChild(inputs[0]);
			inputsDiv.appendChild(document.createTextNode(" %"));
				inputsDiv.appendChild(document.createElement("br"));
	inputsDiv.appendChild(document.createTextNode("Height "));
		inputsDiv.appendChild(inputs[1]);
			inputsDiv.appendChild(document.createTextNode(" %"));
				inputsDiv.appendChild(document.createElement("br"));
	inputsDiv.appendChild(document.createTextNode("Left "));
		inputsDiv.appendChild(inputs[2]);
			inputsDiv.appendChild(document.createTextNode(" %"));
				inputsDiv.appendChild(document.createElement("br"));
	inputsDiv.appendChild(document.createTextNode("Top "));
		inputsDiv.appendChild(inputs[3]);
			inputsDiv.appendChild(document.createTextNode(" %"));
	
	
	
	// Make the movible rect
	var rectPosition = document.createElement('div');
	rectPosition.className = 'config_rectPosition';
	contentDivRect.appendChild(rectPosition);
	
	// Make the resizers divs
	var resizers = ["center_top", "center_bottom", "left_center", "right_center"];
	var resizers_divs = {};
	for(var i in resizers){
		resizers_divs[resizers[i]] = document.createElement('div');
		resizers_divs[resizers[i]].className = resizers[i];
		rectPosition.appendChild(resizers_divs[resizers[i]]);
	}
	
	// Asign the initial sizes and position
	rectPosition.style.width  = parameters["width"]+"%";
	rectPosition.style.height = parameters["height"]+"%";
	rectPosition.style.left   = parameters["left"]+"%";
	rectPosition.style.top    = parameters["top"]+"%";
	
	// Setting the values to the inputs
	set_info_inputs_values();
	
	
	
	resizers_divs["center_center"] = rectPosition;
	
	// Allow reescale through resizers_divs.
	for(var i in resizers_divs){
		(function(resizers_divs, i){
			resizers_divs[i].onmousedown = function(e){
				if(e.target === resizers_divs[i]){
					var extra_position = [
						e.clientX,
						e.clientY,
						rectPosition.offsetLeft,
						rectPosition.offsetTop,
						percentaje_to_screen(parsePercentage(rectPosition.style.width), 'x'),
						percentaje_to_screen(parsePercentage(rectPosition.style.height), 'y')
					];
					switch(i){
						case "center_center":
							contentDivRect.onmousemove = function(e){
								rectPosition.style.top    = screen_to_percentaje(extra_position[3] -extra_position[1] +e.clientY, 'y') +'%';
								rectPosition.style.left   = screen_to_percentaje(extra_position[2] -extra_position[0] +e.clientX, 'x') +'%';
								set_info_inputs_values();
							};
						break;
						case "center_top":
							contentDivRect.onmousemove = function(e){
								rectPosition.style.top    = screen_to_percentaje(extra_position[3] -extra_position[1] +e.clientY, 'y') +'%';
								rectPosition.style.height = screen_to_percentaje(extra_position[5] +extra_position[1] -e.clientY, 'y') +'%';
								set_info_inputs_values();
							};
						break;
						case "center_bottom":
							contentDivRect.onmousemove = function(e){
								rectPosition.style.height = screen_to_percentaje(extra_position[5] -extra_position[1] +e.clientY, 'y') +'%';
								set_info_inputs_values();
							};
						break;
						case "left_center":
							contentDivRect.onmousemove = function(e){
								rectPosition.style.left   = screen_to_percentaje(extra_position[2] -extra_position[0] +e.clientX, 'x') +'%';
								rectPosition.style.width  = screen_to_percentaje(extra_position[4] +extra_position[0] -e.clientX, 'x') +'%';
								set_info_inputs_values();
							};
						break;
						case "right_center":
							contentDivRect.onmousemove = function(e){
								rectPosition.style.width  = screen_to_percentaje(extra_position[4] -extra_position[0] +e.clientX, 'x') +'%';
								set_info_inputs_values();
							};
						break;
					}
					contentDivRect.onmouseup = function(){
						contentDivRect.onmousemove = null;
					}
				}
			};
		})(resizers_divs, i);
	}
	
	// Set the values for the inputs of the Info div in percentage and also truncates the values to 3 decimal places
	// If rectPosition.style.left in % > 50 then moves infoDiv to the left, otherwise to the right  
	function set_info_inputs_values(){
		inputs[0].value = Math.floor(parsePercentage(rectPosition.style.width)*1000)/1000;
		inputs[1].value = Math.floor(parsePercentage(rectPosition.style.height)*1000)/1000;
		inputs[2].value = Math.floor(parsePercentage(rectPosition.style.left)*1000)/1000;
		inputs[3].value = Math.floor(parsePercentage(rectPosition.style.top)*1000)/1000;
		
		if(parsePercentage(rectPosition.style.left) > 50){
			if(infoDiv.style.left === ''){
				infoDiv.style.right = '';
				infoDiv.style.left  = '2%';
			}
		}
		else{
			if(infoDiv.style.right === ''){
				infoDiv.style.right = '2%';
				infoDiv.style.left  = '';
			}
		}
	}
	
	function parsePercentage(value){
		return - -value.substr(0, value.length -1);
	}
	
	function screen_to_percentaje(px, axis){
		switch(axis){
			case 'x':
				return px/window.innerWidth*100; //(px/window.innerWidth)*100
			break;
			case 'y':
				return px/window.innerHeight*100; //(px/window.innerHeight)*100
			break;
		}
	}
	
	function percentaje_to_screen(percentage, axis){
		switch(axis){
			case 'x':
				return percentage*window.innerWidth/100; //(percentage*window.innerWidth)/100
			break;
			case 'y':
				return percentage*window.innerHeight/100; //(percentage*window.innerHeight)/100
			break;
		}
	}
	
}


generate_position_rect({
	"width"  : 60,
	"height" : 60,
	"left"   : 20,
	"top"    : 20
});


// SET GEAR BUTTON ACTION

gearDiv.onclick = function(){
	contentDiv.className += ' visible';
	contentwidgetsDiv.style.display = '';
	
	// Fill the config window
	
	// Go over the array CONFIG
	for(widget in CONFIG){
		var buttonWidget = document.createElement('div');
		buttonWidget.innerHTML += CONFIG[widget]['name'].toUpperCase();
		contentwidgetsDiv.appendChild(buttonWidget);
		
		// set the onclick for the button. executes the widget function and appends the result to the 
		buttonWidget.onclick = (function(widget){
			return function(){
				contentwidgetsDiv.style.display = 'none';
				configwidgetDiv.style.display = 'inherit';
				configwidgetDiv.innerHTML = '<div class="widgetnamebig">' + CONFIG[widget]['name'].toUpperCase() + ' WIDGET</div>'
				// Execute the function and append the result to the corresponding div container
				var divContainerConfigWidget = document.createElement('div');
				divContainerConfigWidget.className = 'widgetcontentconfig';
				divContainerConfigWidget.appendChild(CONFIG[widget]['function']({
					'positioning':generate_position_rect
				}));
				configwidgetDiv.appendChild(divContainerConfigWidget);
				
				// Create the back button for the widget config window
				backbutton = document.createElement('i');
				backbutton.className = 'fa fa-reply backbutton';
				contentDiv.appendChild(backbutton);
				backbutton.onclick = function(){
					// Reset content
					configwidgetDiv.innerHTML = '';
					contentwidgetsDiv.style.display = '';
					configwidgetDiv.style.display = 'none';
					backbutton.remove();
				};
			};
		})(widget);
	}
	
	
};





// Function for the config widgetID. It returns the html object to append on the config window.
// In this case we only want to allow the user to change the position of the widget.
var CONFIG_function = function(){
	var div = document.createElement('div');
	return div;
}

// CREATE AND APPEND GEAR TO THE BODY


// Import css (css of the widget)
// Import css (Github icons pack)
API.Widget.linkMyCSS('css.css').linkExternalCSS("//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css");


// Make the gear button
var gearDiv = API.Widget.create();
gearDiv.addClass('config_buttongear');
gearDiv.innerHTML = '<i class="fa fa-cog"></i>';





// CREATE AND APPEND CONTENT CONFIGURATION DIV

// Make the div container for the config window
var contentDiv = API.Widget.create();
contentDiv.addClass('config_contentdiv');

// Make the div container for the widgets in the config window and append the container for the widgets in the config window
var contentwidgetsDiv = API.document.createElement('div');
contentwidgetsDiv.addClass('contentwidgets');
contentDiv.appendChild(contentwidgetsDiv);

// Make the div container for the result of the configuration function of each widget (share the div) and append the container for the widgets in the config window
var configwidgetDiv = API.document.createElement('div');
configwidgetDiv.addClass('configwidget');
configwidgetDiv.hide();
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
	configwidgetDiv.hide();
	if(backbutton){
		backbutton.remove();
	}
	// Hide window
	contentDiv.removeClass('visible');
};



// Function that cleans the config window and shows redimensionable and movible rectangle.
// Calls callback with the position and size of the rectangle once the user is done or false if the user canceled the operation.
// Used by the widgets that can and wants to change their position and size.
/*
params:
{
	"width"   : number, // %
	"height"  : number, // %
	"left"    : number, // %
	"top"     : number, // %
	"fixed"   : ["width", "height", "left", "top"], // Optative. Array with fixed parameters (they will not be changeable)
	"minimum" : {"width":"x%", ...}, // Optative. Minimum percetage that the value can reach.
	"maximum" : {"width":"x%", ...},  // Optative. Máximum percetage that the value can reach.
	"show_bg" : true/false, // Optative. Show the background or hide it.
	"realtime": callback // Optional. If setted, it is called with an objet that gives the position and size of the movement to allow the user to move the widget in realtime.
}

Example:
generate_position_rect({
	"width"   : 50,
	"height"  : 20,
	"left"    : 0,
	"top"     : 30,
	"fixed"   : ["height","left"],
	"minimum" : {"width":30},
	"maximum" : {"width":60, "top":80},
	"show_bg" : false,
	"realtime": console.log
	},
	console.log
);

console.log receives:
{
	"width"  : number, // %
	"height" : number, // %
	"left"   : number, // %
	"top"    : number  // %
}

*/
a = generate_position_rect;
function generate_position_rect(params, callback){
	// Preconfigured parameters
	var default_position_size = 20; // %

	// Parse params
	var p_fixed = typeof params["fixed"] === "object";
	var p_minimum = typeof params["minimum"] === "object";
	var p_maximum = typeof params["maximum"] === "object";
	var p_show_bg = typeof params["show_bg"] === "boolean";
	var p_realtime = typeof params["realtime"] === "function";
	
	
	// CREATE AND APPEND CONTENT CONFIGURATION DIV

	// Make the div container for the rect
	var contentDivRect = API.Widget.create();
	contentDivRect.addClass('config_contentDivRect');
	if(p_show_bg && params["show_bg"] === false){
		contentDivRect.style.backgroundColor = 'transparent';
	}
	else{
		contentDivRect.style.backgroundImage = 'url(' + API.url('grid.png') + ')';
	}
	
	// Hide contentDiv. Undo this before calling callback
	contentDiv.hide();
	
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
	
	// Callbacks
	buttonOK.onclick = function(){
		contentDivRect.remove();
		contentDiv.unHide();
		if(typeof callback === "function"){
			callback(rectPosition.getPositionSize());
		}
	}
	buttonCANCEL.onclick = function(){
		contentDivRect.remove();
		contentDiv.unHide();
		if(typeof callback === "function"){
			callback(false);
		}
	}
	
	// Make the div container for the inputs
	var inputsDiv = document.createElement('div');
	inputsDiv.className = 'config_rectInfo_inputs';
	infoDiv.appendChild(inputsDiv);
	
	// Generate the interior of the Info div
	var inputs = []; // W, H, L ,R
	var inputs_relation = {0:"width",1:"height",2:"left",3:"top"};
	for(var i = 0; i < 4; ++i){
		inputs[i] = document.createElement("input");
		// Allows to write to the inputs
		if(p_fixed && params["fixed"].indexOf(inputs_relation[i]) !== -1){
			inputs[i].setAttribute("disabled", "disabled");
		}
		else{
			inputs[i].onkeyup = inputs[i].onblur = (function(i){
				return function(){
					change_rectPosition_value(inputs[i].value, inputs_relation[i]);
				}
			})(i);
		}
	}
	
	var appends = ["Width ","Height ","Left ","Top "];
	for(var i in appends){
		inputsDiv.appendChild(document.createTextNode(appends[i]));
		inputsDiv.appendChild(inputs[i]);
		inputsDiv.appendChild(document.createTextNode(" %"));
		if(i !== 3){
			inputsDiv.appendChild(document.createElement("br"));
		}
	}
	
	
	// Make the movible rect
	var rectPosition = API.document.createElement('div');
	rectPosition.addClass('config_rectPosition');
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
	for(var i in inputs_relation){
		rectPosition.style[inputs_relation[i]] = (params[inputs_relation[i]] ? params[inputs_relation[i]] : default_position_size) + "%";
	}
	
	// Setting the values to the inputs
	set_info_inputs_values();
	
	
	
	resizers_divs["center_center"] = rectPosition;
	
	// Allow escalating through dragging resizers_divs.
	for(var i in resizers_divs){
		(function(i){
			resizers_divs[i].onmousedown = function(e){
				if(e.target === resizers_divs[i]){
					var extra_position = [
						e.clientX,
						e.clientY,
						rectPosition.offsetLeft,
						rectPosition.offsetTop,
						rectPosition.clientWidth,
						rectPosition.clientHeight
					];
					switch(i){
						case "center_center":
							contentDivRect.onmousemove = function(e){
								change_rectPosition_value(screen_to_percentaje(extra_position[3] -extra_position[1] +e.clientY, 'y'), "top");
								change_rectPosition_value(screen_to_percentaje(extra_position[2] -extra_position[0] +e.clientX, 'x'), "left");
								set_info_inputs_values();
							};
						break;
						case "center_top":
							contentDivRect.onmousemove = function(e){
								change_rectPosition_value(screen_to_percentaje(extra_position[3] -extra_position[1] +e.clientY, 'y'), "top");
								change_rectPosition_value(screen_to_percentaje(extra_position[5] +extra_position[1] -e.clientY, 'y'), "height");
								set_info_inputs_values();
							};
						break;
						case "center_bottom":
							contentDivRect.onmousemove = function(e){
								change_rectPosition_value(screen_to_percentaje(extra_position[5] -extra_position[1] +e.clientY, 'y'), "height");
								set_info_inputs_values();
							};
						break;
						case "left_center":
							contentDivRect.onmousemove = function(e){
								change_rectPosition_value(screen_to_percentaje(extra_position[2] -extra_position[0] +e.clientX, 'x'), "left");
								change_rectPosition_value(screen_to_percentaje(extra_position[4] +extra_position[0] -e.clientX, 'x'), "width");
								set_info_inputs_values();
							};
						break;
						case "right_center":
							contentDivRect.onmousemove = function(e){
								change_rectPosition_value(screen_to_percentaje(extra_position[4] -extra_position[0] +e.clientX, 'x'), "width");
								set_info_inputs_values();
							};
						break;
					}
					contentDivRect.onmouseup = function(){
						contentDivRect.onmousemove = null;
					}
				}
			};
		})(i);
	}
	
	// Set the values for the inputs of the Info div in percentage and also truncates the values to 3 decimal places
	// If rectPosition.style.left in % > 50 then moves infoDiv to the left, otherwise to the right  
	function set_info_inputs_values(){
		var inputs_v = rectPosition.getPositionSize(); 
		for(var i in inputs_relation){
			inputs[i].value = inputs_v[inputs_relation[i]];
		}
		
		// Change the position of infoDiv to not block the view of rectPosition
		if(rectPosition.getPosition.left() + (rectPosition.getSize.width()/2) > 50){
			if(infoDiv.style.left === ''){
				infoDiv.style.right = '';
				infoDiv.style.left  = '2%';
			}
		}
		else if(infoDiv.style.right === ''){
			infoDiv.style.right = '2%';
			infoDiv.style.left  = '';
		}
	}
	
	function parsePercentage(value){
		return - -value.substr(0, value.length -1);
	}
	
	function screen_to_percentaje(px, axis){
		//(px/window.innerWidth)*100
		return axis === 'x' ? px/window.innerWidth*100 : px/window.innerHeight*100;
	}
	/*
	// Not in use
	function percentaje_to_screen(percentage, axis){
		//(percentage*window.innerWidth)/100
		return axis === 'x' ? percentage*window.innerWidth/100 : percentage*window.innerHeight/100;
	}
	*/
	
	function change_rectPosition_value(value, name){
		if(p_fixed && params["fixed"].indexOf(name) !== -1){
			return;
		}
		if(p_minimum && typeof params["minimum"][name] === "number" && params["minimum"][name] > value){
			rectPosition.style[name] = params["minimum"][name] + "%";
		}
		else if(p_maximum && typeof params["maximum"][name] === "number" && params["maximum"][name] < value){
			rectPosition.style[name] = params["maximum"][name] + "%";
		}
		else{
			rectPosition.style[name] = value + "%";
		}
		send_realtime_calback();
	}
	
	function send_realtime_calback(){
		if(p_realtime){
			params["realtime"](rectPosition.getPositionSize());
		}
	}
}



// SET GEAR BUTTON ACTION

gearDiv.onclick = function(){
	contentDiv.addClass('visible');
	contentwidgetsDiv.unHide();
	
	// Fill the config window
	
	// Go over the array CONFIG
	for(widget in CONFIG){
		var buttonWidget = document.createElement('div');
		buttonWidget.innerHTML += CONFIG[widget]['name'].toUpperCase();
		contentwidgetsDiv.appendChild(buttonWidget);
		
		// set the onclick for the button. executes the widget function and appends the result to the 
		buttonWidget.onclick = (function(widget){
			return function(){
				contentwidgetsDiv.hide();
				configwidgetDiv.unHide();
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
					contentwidgetsDiv.unHide();
					configwidgetDiv.hide();
					backbutton.remove();
				};
			};
		})(widget);
	}
	
	
};
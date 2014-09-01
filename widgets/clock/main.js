var C = crel2;

API.widget.linkMyCSS('css.css');

var ventana = API.widget.create();
ventana.addClass('clock');

var pos = {};
API.storage.remoteStorage.get('pos', function(entrada){
	if(entrada){
		pos = entrada;
	} else {
		pos = {
			"width"  : 20,
			"height" : 20,
			"left"   : 78,
			"top"    : 2
		}
	}
	ventana.setPositionSize(pos);
	
	paint();
	setInterval(paint, 1000);
});

function paint(){
	var today = new Date();

	var date = today.getDate() + " of "
				+ ["January","February","March","April","May","June","July","August","September","October","November","December"][today.getMonth()]
				+ ", " + today.getFullYear();

	var h = today.getHours();
	h = h > 9 ? h : '0' + h;
	
	var	m = today.getMinutes();
	m = m > 9 ? m : '0' + m;
	
	var s = today.getSeconds();
	s = s > 9 ? ':' + s : ':0' + s;
	
	var hour = h + ':' + m;
	
	ventana.innerHTML = '';
	
	C(ventana,
		C('div', ['class', 'hour'], hour, C('span', s)),
		C('div', ['class', 'date'], date)
	);
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

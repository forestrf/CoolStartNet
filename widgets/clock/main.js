var C = crel2;

API.widget.linkMyCSS('css.css');

var clock = API.widget.create();
clock.addClass('clock');


paint();
setInterval(paint, 1000);

function paint(){
	var today = new Date();

	var date = today.getDate() + " of "
				+ ["January","February","March","April","May","June","July","August","September","October","November","December"][today.getMonth()]
				+ ", " + today.getFullYear();

	var h = today.getHours();
	h = h > 9 ? h : '0' + h;
	
	var	m = today.getMinutes();
	m = m > 9 ? m : '0' + m;
	
	var s = today.getSeconds();
	s = s > 9 ? ':' + s : ':0' + s;
	
	var hour = h + ':' + m;
	
	clock.innerHTML = '';
	
	C(clock,
		C('div', ['class', 'hour'], hour, C('span', s)),
		C('div', ['class', 'date'], date)
	);
}

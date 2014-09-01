var C = crel2;

API.widget.linkMyCSS('css.css');

var coolstart_title = API.widget.create();
coolstart_title.addClass('coolstart_title');

C(coolstart_title,
	C('h1', "CoolStart.net"),
	C('h2', "Your customizable home page")
);
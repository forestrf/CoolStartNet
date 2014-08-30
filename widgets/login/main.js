var C = crel2;

API.widget.linkMyCSS('css.css');


var login = API.widget.create();
login.addClass('login');

C(login,
	C('form', ['method', 'post', 'action', 'https://' + API.domain + 'login.php'],
		C('input', ['type', 'text',     'name', 'nick',     'placeholder', 'User']),
		C('input', ['type', 'password', 'name', 'password', 'placeholder', 'Password']),
		C('input', ['type', 'submit', 'name', 'submit', 'value', 'Login'])
	)
);



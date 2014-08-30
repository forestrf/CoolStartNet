var C = crel2;

API.widget.linkMyCSS('css.css');


var login = API.widget.create();
login.addClass('login');

var checkbox ,captcha_placeholder,
email, button;

C(login,
	C('form', ['method', 'post', 'action', 'https://' + API.domain + 'login.php'],
		C('input', ['type', 'text',     'name', 'nick',      'placeholder', 'User',     'tabindex', 1]),
		C('input', ['type', 'password', 'name', 'password',  'placeholder', 'Password', 'tabindex', 2]),
		email = C('input', ['type', 'text', 'name', 'email', 'placeholder', 'email', 'class', 'invisible']),
		captcha_placeholder = C('div', ['class', 'captcha_placeholder']),
		C('label',
			checkbox = C('input', ['type', 'checkbox']),
			"I don't have an account"
		),
		button = C('input', ['type', 'submit', 'name', 'submit', 'value', 'Login', 'tabindex', 3])
	)
);

checkbox.checked = false;

checkbox.onclick = function(){
	if(checkbox.checked){
		button.value = 'Register';
		email.className = '';
		login.style.marginTop = '-20px';
	}
	else{
		button.value = 'Login';
		email.className = 'invisible';
		login.style.marginTop = '0px';
	}
}
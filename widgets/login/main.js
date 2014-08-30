var C = crel2;

API.widget.linkMyCSS('css.css');

var js = document.createElement("script");
js.type = "text/javascript";
js.src = 'http://www.google.com/recaptcha/api/js/recaptcha_ajax.js';
document.body.appendChild(js);

var login = API.widget.create();
login.addClass('login');

var checkbox ,captcha_placeholder,
email, button;

C(login,
	C('form', ['method', 'post', 'action', 'https://' + API.domain + 'login.php'],
		C('input', ['type', 'text',     'name', 'nick',      'placeholder', 'User',     'class', 'c', 'tabindex', 1]),
		C('input', ['type', 'password', 'name', 'password',  'placeholder', 'Password', 'class', 'c', 'tabindex', 2]),
		email = C('input', ['type', 'text', 'name', 'email', 'placeholder', 'email',    'class', 'c invisible']),
		captcha_placeholder = C('div', ['class', 'captcha_placeholder']),
		C('label', ['class', 'c'],
			checkbox = C('input', ['type', 'checkbox']),
			"I don't have an account"
		),
		button = C('input', ['type', 'submit', 'name', 'submit', 'value', 'Login', 'class', 'c', 'tabindex', 3])
	)
);

checkbox.checked = false;

checkbox.onclick = function(){
	if(checkbox.checked){
		button.value = 'Register';
		email.className = 'c';
		login.style.marginTop = '-10%';
		
		button.setAttribute('tabindex', 5);
		email.setAttribute('tabindex', 3);
		
		Recaptcha.create(SERVER_VARS.CAPTCHA_PUB_KEY, captcha_placeholder, {
			theme: "red",
			callback: function(){
				document.getElementById('recaptcha_response_field').setAttribute('tabindex', 4);
			}
		});
	}
	else{
		button.value = 'Login';
		email.className = 'c invisible';
		login.style.marginTop = '0px';
		
		email.removeAttribute('tabindex');
		button.setAttribute('tabindex', 3);
		
		captcha_placeholder.innerHTML = '';
	}
}
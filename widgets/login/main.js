var C = crel2;

API.widget.linkMyCSS('css.css');

var txt_register = 'Register';
var txt_login = 'Login';

var js = document.createElement("script");
js.type = "text/javascript";
js.src = '//www.google.com/recaptcha/api/js/recaptcha_ajax.js';
document.body.appendChild(js);

var login = API.widget.create();
login.addClass('login');

var checkbox ,captcha_placeholder, mail, user, pass, button, form;

C(login,
	form = C('form', ['method', 'post', 'action', 'https://' + API.domain + 'login.php'],
		user = C('input', ['type', 'text',     'name', 'nick',      'placeholder', 'User',     'class', 'c', 'tabindex', 1]),
		pass = C('input', ['type', 'password', 'name', 'password',  'placeholder', 'Password', 'class', 'c', 'tabindex', 2]),
		mail = C('input', ['type', 'text',     'name', 'email',     'placeholder', 'email',    'class', 'c invisible']),
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
		button.value = txt_register;
		mail.className = 'c';
		login.style.marginTop = '-10%';
		
		button.setAttribute('tabindex', 5);
		mail.setAttribute('tabindex', 3);
		
		Recaptcha.create(SERVER_VARS.CAPTCHA_PUB_KEY, captcha_placeholder, {
			theme: "red",
			callback: function(){
				document.getElementById('recaptcha_response_field').setAttribute('tabindex', 4);
			}
		});
	}
	else{
		button.value = txt_login;
		mail.className = 'c invisible';
		login.style.marginTop = '0px';
		
		mail.removeAttribute('tabindex');
		button.setAttribute('tabindex', 3);
		
		captcha_placeholder.innerHTML = '';
	}
}

form.onsubmit = function(){
	button.value = 'Wait...';
	
	var elems = [button, user, pass, mail];
	
	if(document.getElementById('recaptcha_response_field'))
		elems.push(document.getElementById('recaptcha_response_field'));
	
	var data = '';
	for(var i = 0; i < elems.length; i++){
		data += elems[i].getAttribute("name") + '=' + encodeURIComponent(elems[i].value) + '&';
	}
	
	API.xhr('login_js.php', data, function(data){
		data = JSON.parse(data);
		if(data.status === 'OK'){
			location.href = '//' + API.domain;
		}
		else{
			fail();
		}
	}, fail);
	
	return false;
}

function fail(){
	user.className = 'c fail';
	pass.className = 'c fail';
	
	button.value = checkbox.checked ? txt_register : txt_login;
	
	setTimeout(function(){
		user.className = 'c';
		pass.className = 'c';
	}, 1500);
}

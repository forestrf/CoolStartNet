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

var checkbox ,captcha_placeholder, mail, user, pass, button, form, messages;

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
	),
	messages = C('div', ['class', 'message'])
);

API.document.wrapElement(user).wrapElement(pass).wrapElement(mail);

checkbox.checked = false;

checkbox.onclick = function(){
	if(checkbox.checked){
		button.value = txt_register;
		mail.removeClass('invisible');
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
		mail.addClass('invisible');
		login.style.marginTop = '0px';
		
		mail.removeAttribute('tabindex');
		button.setAttribute('tabindex', 3);
		
		captcha_placeholder.innerHTML = '';
	}
}

function submit(){
	button.value = 'Wait...';
	
	var elems = [button, user, pass];
	
	if(checkbox.checked){
		elems.push(document.getElementById('recaptcha_response_field'));
		elems.push(document.getElementById('recaptcha_challenge_field'));
		elems.push(mail);
	}
	
	var data = '';
	for(var i = 0; i < elems.length; i++){
		data += elems[i].getAttribute("name") + '=' + encodeURIComponent(elems[i].value) + '&';
	}
	data = data.substr(0, data.length -1);
	
	var url = checkbox.checked ? 'https://'+API.domain+'register_js.php' : 'https://'+API.domain+'login_js.php';
	
	API.xhr(url, data, function(data){
		data = JSON.parse(data);
		if(data.status === 'OK'){
			if(checkbox.checked){
				checkbox.checked = false;
				submit();
			}
			else{
				location.href = '//' + API.domain;
			}
		}
		else{
			fail(data.problem);
		}
	}, function(){fail('Server unreachable');});
	
	return false;
}

form.onsubmit = submit;

function fail(txt){
	user.addClass('fail');
	pass.addClass('fail');
	mail.addClass('fail');
	
	messages.innerHTML = txt;
	
	if(checkbox.checked){
		Recaptcha.reload();
	}
	
	button.value = checkbox.checked ? txt_register : txt_login;
	
	setTimeout(function(){
		user.removeClass('fail');
		pass.removeClass('fail');
		mail.removeClass('fail');
		messages.innerHTML = '';
	}, 4000);
}

var C = crel2;

API.widget.linkMyCSS('css.css');

var txt_register = 'Register';
var txt_login = 'Login';
var txt_remember = 'Remember';
var fail_class = 'fail';
var invisible = 'invisible';
var tabindex = 'tabindex';

var js = document.createElement("script");
js.type = "text/javascript";
js.src = '//www.google.com/recaptcha/api/js/recaptcha_ajax.js';
document.body.appendChild(js);

var login = API.widget.create();
login.addClass('login');

var register ,captcha_placeholder, mail, user, pass, button, form, messages, forgot;

C(login,
	form = C('form', ['onsubmit', 'return false;'],
		user = C('input', ['type', 'text',     'name', 'nick',      'placeholder', 'User',     'class', 'c', tabindex, 1]),
		pass = C('input', ['type', 'password', 'name', 'password',  'placeholder', 'Password', 'class', 'c', tabindex, 2]),
		mail = C('input', ['type', 'text',     'name', 'email',     'placeholder', 'email',    'class', 'c ' + invisible]),
		captcha_placeholder = C('div', ['class', 'captcha_placeholder']),
		C('label', ['class', 'c'],
			register = C('input', ['type', 'checkbox']),
			"I don't have an account"
		),
		C('label', ['class', 'c forgotten'],
			forgot = C('input', ['type', 'checkbox']),
			"I forgot my password"
		),
		button = C('input', ['type', 'submit', 'name', 'submit', 'value', 'Login', 'class', 'c', tabindex, 3])
	),
	messages = C('div', ['class', 'message'])
);

API.document.wrapElement(user).wrapElement(pass).wrapElement(mail).wrapElement(messages);

register.checked = false;

register.onclick = function(){
	if(register.disabled) return;
	
	if(register.checked){
		forgot.disabled = true;
		
		login.style.marginTop = '-10%';
		
		button.value = txt_register;
		mail.removeClass(invisible);
		
		button.setAttribute(tabindex, 5);
		mail.setAttribute(tabindex, 3);
		
		Recaptcha.create(SERVER_VARS.CAPTCHA_PUB_KEY, captcha_placeholder, {
			theme: "red",
			callback: function(){
				document.getElementById('recaptcha_response_field').setAttribute(tabindex, 4);
			}
		});
	}
	else{
		backToNormal();
	}
}

forgot.onclick = function(){
	if(forgot.disabled) return;
	
	if(forgot.checked){
		register.disabled = true;
		
		login.style.marginTop = '-5%';
		
		button.value = txt_remember;
		mail.removeClass(invisible);
		pass.addClass(invisible);
		user.addClass(invisible);
		
		pass.removeAttribute(tabindex);
		user.removeAttribute(tabindex);
		button.setAttribute(tabindex, 3);
		mail.setAttribute(tabindex, 1);
		
		Recaptcha.create(SERVER_VARS.CAPTCHA_PUB_KEY, captcha_placeholder, {
			theme: "red",
			callback: function(){
				document.getElementById('recaptcha_response_field').setAttribute(tabindex, 2);
			}
		});
	}
	else{
		backToNormal();
	}
}

function backToNormal(){
	register.disabled = false;
	forgot.disabled = false;
	
	login.style.marginTop = '0px';
	
	button.value = txt_login;
	mail.addClass(invisible);
	pass.removeClass(invisible);
	user.removeClass(invisible);
	
	mail.removeAttribute(tabindex);
	button.setAttribute(tabindex, 3);
	pass.setAttribute(tabindex, 2);
	user.setAttribute(tabindex, 1);
	
	captcha_placeholder.innerHTML = '';
}

function submit(){
	button.value = 'Wait...';
	
	var elems = [button, user, pass];
	
	if(register.checked || forgot.checked){
		elems.push(document.getElementById('recaptcha_response_field'));
		elems.push(document.getElementById('recaptcha_challenge_field'));
		elems.push(mail);
	}
	
	var data = '';
	for(var i = 0; i < elems.length; i++){
		data += elems[i].getAttribute("name") + '=' + encodeURIComponent(elems[i].value) + '&';
	}
	data = data.substr(0, data.length -1);
	
	url = 'https://' + SERVER_VARS.DOMAIN_PATH;
	if(register.checked){
		url += 'register_js.php';
	} else if(forgot.checked){
		url += 'forgot_js.php';
	} else {
		url += 'login_js.php';
	}
	
	API.xhr(url, data, function(data){
		data = JSON.parse(data);
		if(data.status === 'OK'){
			if(register.checked){
				ok('Please, check you e-mail inbox to validate your account');
				register.checked = false;
			} else if(forgot.checked){
				ok('Please, check you e-mail to restore your account');
				forgot.checked = false;
			}else{
				ok('Logged in. Refreshing...');
				location.href = '//' + SERVER_VARS.DOMAIN_PATH;
			}
		}
		else{
			fail(data.problem);
		}
	}, function(){fail('Server unreachable');});
	
	return false;
}

form.onsubmit = submit;

function ok(txt){
	messages.removeClass('fail');
	messages.addClass('ok');
	messages.innerHTML = txt;

	of();
}

function fail(txt){
	user.addClass(fail_class);
	pass.addClass(fail_class);
	mail.addClass(fail_class);
	
	messages.removeClass('ok');
	messages.addClass('fail');
	messages.innerHTML = txt;
	
	setTimeout(function(){
		user.removeClass(fail_class);
		pass.removeClass(fail_class);
		mail.removeClass(fail_class);
	}, 4000);
	
	of();
}

function of(){
	if(register.checked){
		button.value = txt_register;
		Recaptcha.reload();
	} else if(forgot.checked){
		button.value = txt_remember;
		Recaptcha.reload();
	} else {
		button.value = txt_login;
	}
}

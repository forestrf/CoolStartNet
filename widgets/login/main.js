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
		
		Recaptcha.create(API.globals.captchaPublicKey, captcha_placeholder, {
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
		
		Recaptcha.create(API.globals.captchaPublicKey, captcha_placeholder, {
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
	
	register.checked = false;
	forgot.checked = false;
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
	
	var url = "";
	if(register.checked){
		url = API.siteURLs.register;
	} else if(forgot.checked){
		url = API.siteURLs.forgot;
	} else {
		url = API.siteURLs.login;
	}
	
	API.xhr(url, data, function(data){
		if(data.status === 'OK'){
			if(register.checked){
				ok('Please, check you e-mail inbox to validate your account');
				backToNormal();
			} else if(forgot.checked){
				ok('Please, check you e-mail to restore your account');
				backToNormal();
			}else{
				ok('Logged in. Refreshing...');
				location.href = API.siteURLs.main;
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

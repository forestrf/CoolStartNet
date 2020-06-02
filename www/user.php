<?php

require_once __DIR__.'/php/defaults.php';
require_once __DIR__.'/php/config.php';
require_once __DIR__.'/php/lib/DB.php';
require_once __DIR__.'/php/functions/generic.php';
require_once __DIR__.'/php/lib/recaptcha-php/recaptchalib.php';

# option === function name

$options = array(	
	'login'     => 0 // JSON
	,'register' => 0 // JSON
	,'forgot'   => 0 // JSON
	,'logout'   => 0 // REDIRECT
	,'validate' => 0 // REDIRECT
	,'recover'  => 0 // REDIRECT
	
	,'update-password' => 0 // JSON
	,'update-email'    => 0 // JSON
	,'delete-account'  => 0 // JSON
);



// Validate GET option

if (isset($_GET['action'])) {
	$action = &$_GET['action'];
	if (!isset($options[$action])) {
		end_fail('Invalid action specified');
	}
} else {
	end_fail('Not action specified');
}



// Launch option function

$action = str_replace('-', '_', $action);
$action();





/////////////////////////////////////////////////////////
//
// FUNCTIONS OF THE OPTIONS
//
/////////////////////////////////////////////////////////

function login(){
	$db = open_db_session();
	if(
		filter_input(INPUT_POST, 'nick', FILTER_CALLBACK, array('options' => 'filter_nick'))
		&& filter_input(INPUT_POST, 'password', FILTER_CALLBACK, array('options' => 'filter_password'))
	) {
		/*
		if ($_POST['nick'] == DEFAULT_USER_NICK && !DEFAULT_USER_ACCESSIBLE) {
			exit;
		}
		*/
		
		if (tries_get() > 0) {
			$user = $db -> check_nick_password($_POST['nick'], $_POST['password']);
			
			if ($user !== false) {
				G::$SESSION->create_session($user['IDuser']);
				tries_reset();
				end_ok();
			} else {
				// Loggin attempt reduce
				tries_decrease();
				
				if (tries_get() > 0) {
					end_fail('Invalid login. '.tries_get().' attempts left');
				}
			}
		} end_fail('Your IP is banned for '.LOGIN_FAIL_WAIT.' minutes');
	} end_fail('Incomplete login attempt');
}

function register(){
	if (!USERS_CAN_REGISTER) {
		end_fail('Registration is closed');
	}

	if (
		isset($_POST['g-recaptcha-response'])
		&& filter_input(INPUT_POST, 'nick', FILTER_CALLBACK, array('options' => 'filter_nick'))
		&& filter_input(INPUT_POST, 'password', FILTER_CALLBACK, array('options' => 'filter_password'))
		&& filter_input(INPUT_POST, 'email', FILTER_CALLBACK, array('options' => 'filter_email'))
	) {
		$reCaptcha = new ReCaptcha(CAPTCHA_PRIVATE_KEY);
		$resp = $reCaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $_POST['g-recaptcha-response']);

		if ($resp != null && $resp->success) {
			$db = new DB();
			
			if ($db -> create_new_user($_POST['nick'], $_POST['password'], $_POST['email'], $validation)) {
				
				$validation_link = 'https://'.WEB_PATH.'user?action=validate&nick='.urlencode($_POST['nick']).'&validation='.urlencode($validation);
				
				$subject = 'Validate your account';
				$body = "Validate your account by following the next link:<br/><br/>"
				      . "<a href=\"{$validation_link}\">{$validation_link}</a>";
				
				send_mail($_POST['email'], $subject, $body);
				
				end_ok();
			} else end_fail('The user already exists');
		} else end_fail('Incorrect captcha');
	} else end_fail('Incorrect nick, password or email');
}

function logout(){
	open_db_session();
	G::$SESSION->remove_session();
	header('Location: //'.WEB_PATH, true, 302);
}

function validate(){
	// Do not give info about invalid validations.
	
	if (
		filter_input(INPUT_GET, 'nick', FILTER_CALLBACK, array('options' => 'filter_nick'))
		&& filter_input(INPUT_GET, 'validation', FILTER_CALLBACK, array('options' => 'filter_validation'))
		&& tries_get() > 0
	) {
		$db = new DB();
		
		$email = $db -> validate_new_user($_GET['nick'], $_GET['validation']);
		
		if ($email) {
			$link = 'https://'.WEB_PATH;
			
			$subject = 'Account validated';
			$body = 'Welcome to CoolStart.net!<br/><br/>'
			      . 'Have a good time here!<br/><br/>'
			      . '<a href="'.$link.'">'.$link.'</a>';
			
			send_mail($email, $subject, $body);
			
			tries_reset();
		} else tries_decrease();
	}
	header('Location: //'.WEB_PATH, true, 302);
}

function recover(){
	// Do not give info about invalid recoveries.
	
	if (
		filter_input(INPUT_GET, 'nick', FILTER_CALLBACK, array('options' => 'filter_nick'))
		&& filter_input(INPUT_GET, 'validation', FILTER_CALLBACK, array('options' => 'filter_validation'))
		&& tries_get() > 0
	) {
		$db = new DB();
		
		$email = $db -> recover_account_validate($_GET['nick'], $_GET['validation']);
		
		if ($email) {
			$new_password = random_string(PASSWORD_MIN_LENGTH, G::abcABC09);
			
			$db -> modify_password_nick($_GET['nick'], $new_password);
			
			$subject = 'Here is your new password';
			
			$body = "Your new password of your account \"{$_GET['nick']}\" is:<br/><br/>"
			      . "{$new_password}<br/><br/>"
			      . 'Change it as soon as possible.';
			
			send_mail($email, $subject, $body);
			
			tries_reset();
		} else tries_decrease();
	}
	header('Location: //'.WEB_PATH, true, 302);
}

function forgot(){
	if (
		isset($_POST['g-recaptcha-response'])
		&& filter_input(INPUT_POST, 'email', FILTER_CALLBACK, array('options' => 'filter_email'))
	) {
		$reCaptcha = new ReCaptcha(CAPTCHA_PRIVATE_KEY);
		$resp = $reCaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $_POST['g-recaptcha-response']);

		if ($resp != null && $resp->success) {
			$db = new DB();
			
			if ($db -> recover_account($_POST['email'], $nick, $validation)) {
				$validation_link = 'https://'.WEB_PATH.'user?action=recover&nick='.urlencode($nick).'&validation='.urlencode($validation);
				
				$subject = 'Recover your account';
				$body = "Your nick is: {$nick}<br/>Recover your account by following this link:<br/><br/>"
				      . '<a href="'.$validation_link.'">'.$validation_link.'</a>';
				
				send_mail($_POST['email'], $subject, $body);
			}
			// OK, even if it fails, to prevent information leak.
			end_ok();
		} else end_fail('Incorrect captcha');
	} else end_fail('Incorrect nick, password or email');
}

function update_password() {
	if (
		filter_input(INPUT_POST, 'current-password', FILTER_CALLBACK, array('options' => 'filter_password'))
		&& filter_input(INPUT_POST, 'new-password', FILTER_CALLBACK, array('options' => 'filter_password'))
		&& filter_input(INPUT_POST, 'new-password-2', FILTER_CALLBACK, array('options' => 'filter_password'))
	) {
		if ($_POST['new-password'] === $_POST['new-password-2']) {
			$db = open_db_session();
			if ($db->check_user_password(G::$SESSION->userID, $_POST['current-password'])) {
				$db->modify_password(G::$SESSION->userID, $_POST['new-password']);
				end_ok('Password updated');
			} else end_fail('Incorrect current password');
		} else end_fail('Type the same new password in both fields');
	}
}






/////////////////////////////////////////////////////////
//
// FUNCTIONS TO TRACK FAILED ATTEMPTS BY IP
//
/////////////////////////////////////////////////////////

// Contabilize how many attemps has the user left (by IP)
function tries_get() {
	$tries = MAX_LOGIN_FAILS; return $tries; // Problems with APC
	$tries = apcu_fetch('login_fail_'.$_SERVER['REMOTE_ADDR']);
	if ($tries === false) $tries = MAX_LOGIN_FAILS;
	return $tries;
}
function tries_decrease() {
	return; // Problems with APC
	apcu_store('login_fail_'.$_SERVER['REMOTE_ADDR'], tries_get() - 1, LOGIN_FAIL_WAIT * 60);
}
function tries_reset() {
	return; // Problems with APC
	$tries = apcu_delete('login_fail_'.$_SERVER['REMOTE_ADDR']);
}



/////////////////////////////////////////////////////////
//
// FUNCTIONS OF THE END OPTIONS (JSON ENDS)
//
/////////////////////////////////////////////////////////

function end_ok($txt = ''){
	echo '{"status":"OK","message":'.json_encode($txt, true).'}';
	exit;
}

function end_fail($txt = ''){
	echo '{"status":"FAIL","message":'.json_encode($txt, true).'}';
	exit;
}

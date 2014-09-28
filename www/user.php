<?php

require_once __DIR__.'/php/defaults.php';

# option === function name

$options = array(	
	'login'     => 0 // JSON
	,'register' => 0 // JSON
	,'forgot'   => 0 // JSON
	,'logout'   => 0 // REDIRECT
	,'validate' => 0 // REDIRECT
	,'recover'  => 0 // REDIRECT
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

require_once __DIR__.'/php/functions/generic.php';

$action();





/////////////////////////////////////////////////////////
//
// FUNCTIONS OF THE OPTIONS
//
/////////////////////////////////////////////////////////

function login(){
	$db = open_db_session();
	if(isset($_POST['submit'])
			&& isset($_POST['nick'])
			&& isset($_POST['password'])
			&& isset($_POST['nick'][0])
			&& isset($_POST['password'][0])) {
		
		if ($_POST['nick'] == DEFAULT_USER_NICK && !DEFAULT_USER_ACCESSIBLE) {
			exit;
		}
		
		$apc_key = 'login_fail_'.$_SERVER['REMOTE_ADDR'];
		$tries = apc_fetch($apc_key);
		if ($tries === false) {
			$tries = 1;
		} else {
			$tries++;
		}
		
		if ($tries <= MAX_LOGIN_FAILS) {	
			$valid = $db -> check_nick_password($_POST['nick'], $_POST['password']);
			
			if ($valid !== false) {
				$user = &$valid;
				$user['valid'] = true; //Anonymous user has valid = false
				$accessTokens = $db->getAllAccessToken();
				foreach($accessTokens as $service => $accessToken){
					$user[$service] = $accessToken;
				}
				$_SESSION['user'] = $user;
				apc_delete($apc_key);
				end_ok();
			} else {
				// Loggin attempt reduce
				apc_store($apc_key, $tries, LOGIN_FAIL_WAIT * 60);
				
				if($tries == MAX_LOGIN_FAILS){
					end_fail('Your IP is banned for '.LOGIN_FAIL_WAIT.' minutes');
				} else {
					end_fail('Invalid login. '.(MAX_LOGIN_FAILS - $tries).' attempts left');
				}
			}
		} else end_fail('Your IP is banned for '.LOGIN_FAIL_WAIT.' minutes');
	} else end_fail('Incomplete login attempt');
}

function register(){
	require_once __DIR__.'/php/config.php';
	
	if (!USERS_CAN_REGISTER) {
		echo '{"status":"FAIL","problem":"Registration is closed"}';
	}

	if (isset($_POST['submit'])
			&& isset($_POST['nick'])
			&& isset($_POST['password'])
			&& isset($_POST['email'])
			&& isset($_POST['recaptcha_challenge_field'])
			&& isset($_POST['recaptcha_response_field'])) {
		
		require_once __DIR__.'/php/lib/recaptcha-php/recaptchalib.php';
		
		$resp = recaptcha_check_answer(CAPTCHA_PRIVATE_KEY, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

		if ($resp->is_valid) {
			require_once __DIR__.'/php/lib/DB.php';
			
			if (!isset($_POST['nick'][NICK_MAX_LENGTH+1])
					&& !isset($_POST['password'][PASSWORD_MAX_LENGTH+1])
					&& !isset($_POST['email'][EMAIL_MAX_LENGTH+1])
					&& isset($_POST['nick'][0])
					&& isset($_POST['password'][0])
					&& isset($_POST['email'][0])
					&& filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				
				$db = new DB();
				$db -> Open();
				
				if ($db -> create_new_user($_POST['nick'], $_POST['password'], $_POST['email'], $validation)) {
					
					$validation_link = 'https://'.WEB_PATH.'user?action=validate&nick='.urlencode($_POST['nick']).'&validation='.urlencode(base64_encode($validation));
					
					$subject = 'Validate your account';
					$body = "Validate your account by following the next link:<br/><br/>"
					      . "<a href=\"{$validation_link}\">{$validation_link}</a>";
					
					send_mail($_POST['email'], $subject, $body);
					
					end_ok();
				} else end_fail('The user already exists');
			} else end_fail('Incorrect nick, password or email');
		} else end_fail('Incorrect captcha');
	} else end_fail('There are missing values');
}

function logout(){
	$session = open_db_session('session');
	$session->stop();
	header('Location: //'.WEB_PATH, true, 302);
}

function validate(){
	// Do not give info about invalid validations.
	
	require_once __DIR__.'/php/config.php';
	require_once __DIR__.'/php/lib/DB.php';
	
	if (isset($_GET['nick'])
			&& !isset($_GET['nick'][NICK_MAX_LENGTH+1])
			&& isset($_GET['nick'][0])
			&& isset($_GET['validation'])
			&& isset($_GET['validation'][0])) {
		
		$db = new DB();
		$db -> Open();
		
		$email = $db -> validate_new_user($_GET['nick'], base64_decode($_GET['validation']));
		
		if ($email) {
			$link = 'https://'.WEB_PATH;
			
			$subject = 'Account validated';
			$body = "Welcome to CoolStart.net!<br/><br/>"
				  . "Have a good time here!<br/><br/>"
				  . '<a href="'.$link.'">'.$link.'</a>';
			
			send_mail($email, $subject, $body);
			
			header('Location: https://'.WEB_PATH);
		}
	}
}

function recover(){
	// Do not give info about invalid recoveries.
	
	require_once __DIR__.'/php/config.php';
	require_once __DIR__.'/php/lib/DB.php';
	
	if (isset($_GET['nick'])
			&& !isset($_GET['nick'][NICK_MAX_LENGTH+1])
			&& isset($_GET['nick'][0])
			&& isset($_GET['validation'])
			&& isset($_GET['validation'][0])) {
		
		$db = new DB();
		$db -> Open();
		
		$email = $db -> recover_account_validate($_GET['nick'], base64_decode($_GET['validation']));
		
		if ($email) {
			$new_password = random_string(10, 48, 57);
			
			$db -> modify_password($_GET['nick'], $new_password);
			
			$subject = 'Here is your new password';
			
			$body = "Your new password of your account \"{$_GET['nick']}\" is:<br/><br/>"
			      . "{$new_password}<br/><br/>"
			      . 'Change it as soon as possible.';
			
			send_mail($email, $subject, $body);
			
			header('Location: //'.WEB_PATH, true, 302);
		}
	}
}

function forgot(){
	if (isset($_POST['submit'])
			&& isset($_POST['email'])
			&& isset($_POST['recaptcha_challenge_field'])
			&& isset($_POST['recaptcha_response_field'])) {
		
		require_once __DIR__.'/php/config.php';
		require_once __DIR__.'/php/lib/recaptcha-php/recaptchalib.php';
		
		$resp = recaptcha_check_answer(CAPTCHA_PRIVATE_KEY, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

		if ($resp->is_valid) {
			require_once __DIR__.'/php/lib/DB.php';
			
			if (!isset($_POST['email'][EMAIL_MAX_LENGTH+1])
					&& isset($_POST['email'][0])
					&& filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				
				$db = new DB();
				$db -> Open();
				
				if ($db -> recover_account($_POST['email'], $nick, $validation)) {
					$validation_link = 'https://'.WEB_PATH.'user?action=recover&nick='.urlencode($nick).'&validation='.urlencode(base64_encode($validation));
					
					$subject = 'Recover your account';
					$body = "Your nick is: {$nick}<br/>Recover your account by following the next link:<br/><br/>"
					      . '<a href="'.$validation_link.'">'.$validation_link.'</a>';
					
					send_mail($_POST['email'], $subject, $body);
				}
				// OK, even if it fails, to prevent information leak.
				end_ok();
			} else end_fail('Incorrect nick, password or email');
		} else end_fail('Incorrect captcha');
	} else end_fail('There are missing values');
}



/////////////////////////////////////////////////////////
//
// FUNCTIONS OF THE END OPTIONS (JSON ENDS)
//
/////////////////////////////////////////////////////////

function end_ok(){
	echo '{"status":"OK"}';
	exit;
}

function end_fail($txt){
	echo '{"status":"FAIL","problem":"'.$txt.'"}';
	exit;
}

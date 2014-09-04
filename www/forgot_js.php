<?php
if(
	isset($_POST['submit']) &&
	isset($_POST['email']) &&
	isset($_POST['recaptcha_challenge_field']) &&
	isset($_POST['recaptcha_response_field'])
){
	require_once 'php/config.php';
	require_once 'php/lib/recaptcha-php/recaptchalib.php';
	
	$resp = recaptcha_check_answer(CAPTCHA_PRIVATE_KEY, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

	if($resp->is_valid) {
		require_once 'php/lib/DB.php';
		
		if(
			!isset($_POST['email'][EMAIL_MAX_LENGTH+1]) &&
			isset($_POST['email'][0]) &&
			filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)
		){
			$db = new DB();
			$db -> Open();
			
			if($db -> recover_account($_POST['email'], $nick, $validation)){
				
				$validation_link = 'https://'.WEB_PATH.'recover.php?nick='.urlencode($nick).'&validation='.urlencode(base64_encode($validation));
				
				$subject = 'Recover your account';
				$body = "Recover your account by following the next link\r\n\r\n"
					. '<a href="'.$validation_link.'">'.$validation_link.'</a>';
				
				send_mail($_POST['email'], $subject, $body);
			}
			// OK, even if it fails, to prevent information leak.
			echo '{"status":"OK"}';exit;
		}
		else{
			echo '{"status":"FAIL","problem":"Incorrect nick, password or email"}';exit;
		}
	}
	else{
		echo '{"status":"FAIL","problem":"Incorrect captcha"}';exit;
	}
}
echo '{"status":"FAIL","problem":"There are missing values"}';
?>

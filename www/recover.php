<?php
if(
	isset($_GET['nick']) &&
	isset($_GET['validation'])
){
	require_once 'php/config.php';
	require_once 'php/lib/DB.php';
	
	if(
		!isset($_GET['nick'][NICK_MAX_LENGTH+1]) &&
		isset($_POST['nick'][0]) &&
		isset($_GET['validation'][0])
	){
		$db = new DB();
		$db -> Open();
		
		$email = $db -> recover_account_validate($nick, $validation);
		
		if($email){
			
			$new_password = random_string(10, 48, 57);
			
			$db -> modify_password($nick, $new_password);
			
			$subject = 'Here is your new password';
			
			$body = "Your new password of your account is:<br/><br/>{$new_password}<br/><br/>Change it as soon as possible.";
			
			send_mail($email, $subject, $body);
		}
	}
}
// Do not give info about invalid recoveries.
?>

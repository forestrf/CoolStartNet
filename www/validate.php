<?php
if(
	isset($_GET['nick']) &&
	isset($_GET['validation'])
){
	require_once 'php/config.php';

	require_once 'php/lib/DB.php';
	
	if(
		!isset($_GET['nick'][NICK_MAX_LENGTH+1]) &&
		isset($_GET['nick'][0]) &&
		isset($_GET['validation'][0])
	){
		$db = new DB();
		$db -> Open();
		
		$email = $db -> validate_new_user($_GET['nick'], base64_decode($_GET['validation']));
		
		if($email){
			$link = 'https://'.WEB_PATH;
			
			$subject = 'Account validated';
			$body = "Welcome to CoolStart.net!\r\n\r\n"
				. "Have a good time here!\r\n\r\n"
				. '<a href="'.$link.'">'.$link.'</a>';
			
			send_mail($email, $subject, $body);
			
			header('Location: https://'.WEB_PATH);
		}
	}
}
// Do not give info about invalid validations.
?>

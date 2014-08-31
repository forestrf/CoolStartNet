<?php
require_once 'php/functions/generic.php';
$db = open_db_session();
if(
	isset($_POST['submit']) &&
	isset($_POST['nick']) &&
	isset($_POST['password']) &&
	isset($_POST['nick'][0]) &&
	isset($_POST['password'][0])
){
	if($_POST['nick'] == DEFAULT_USER_NICK && !DEFAULT_USER_ACCESSIBLE){
		exit;
	}
	$valid = $db -> check_nick_password($_POST['nick'], $_POST['password']);
	
	if($valid !== false){
		$_SESSION['user'] = &$valid;
		$accessTokens = $db->getAllAccessToken();
		foreach($accessTokens as $service => $accessToken){
			$_SESSION['user'][$service] = $accessToken;
		}
		echo '{"status":"OK"}';exit;
	}
	else{
		echo '{"status":"FAIL","problem":"Invalid login"}';exit;
	}
}

echo '{"status":"FAIL","problem":"Incomplete login attempt"}';

?>
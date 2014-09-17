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
	
	$apc_key = 'login_fail_'.$_SERVER['REMOTE_ADDR'];
	$tries = apc_fetch($apc_key);
	if($tries === false){
		$tries = 1;
	} else {
		$tries++;
	}
	
	if($tries <= MAX_LOGIN_FAILS){	
		$valid = $db -> check_nick_password($_POST['nick'], $_POST['password']);
		
		if($valid !== false){
			$to_session = array();
			$to_session['user'] = &$valid;
			$to_session['user']['IP'] = $_SERVER['REMOTE_ADDR'];
			$to_session['user']['valid'] = true; //Anonymous user has valid = false
			$accessTokens = $db->getAllAccessToken();
			foreach($accessTokens as $service => $accessToken){
				$to_session['user'][$service] = $accessToken;
			}
			$_SESSION['user'] = $to_session;
			apc_delete($apc_key);
			echo '{"status":"OK"}';
		}
		else{
			// Loggin attempt reduce
			apc_store($apc_key, $tries, LOGIN_FAIL_WAIT * 60);
			
			if($tries == MAX_LOGIN_FAILS){
				end_fail(2);
			} else {
				end_fail(1);
			}
		}
	} else {
		end_fail(2);
	}
} else {
	end_fail(3);
}

function end_fail($n){
	switch($n){
		case 1:
			echo '{"status":"FAIL","problem":"Invalid login. '.(MAX_LOGIN_FAILS - $GLOBALS['tries']).' attempts left"}';exit;
		break;
		case 2:
			echo '{"status":"FAIL","problem":"Your IP is banned for '.LOGIN_FAIL_WAIT.' minutes"}';exit;
		break;
		case 3:
			echo '{"status":"FAIL","problem":"Incomplete login attempt"}';
		break;
	}
	exit;
}

?>
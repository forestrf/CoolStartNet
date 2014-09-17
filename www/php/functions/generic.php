<?php

function isInteger($input){
    return ctype_digit(strval($input));
}

function custom_hmac($hash_func='md5', $data, $key, $raw_output=false){
	$hash_func = strtolower($hash_func);
	$pack = 'H'.strlen($hash_func('test'));
	$size = 64;
	$opad = str_repeat(chr(0x5C), $size);
	$ipad = str_repeat(chr(0x36), $size);
	
	if(strlen($key) > $size){
		$key = str_pad(pack($pack, $hash_func($key)), $size, chr(0x00));
	}
	else{
		$key = str_pad($key, $size, chr(0x00));
	}
	
	for($i = 0; $i < strlen($key) - 1; $i++){
		$opad[$i] = $opad[$i] ^ $key[$i];
		$ipad[$i] = $ipad[$i] ^ $key[$i];
	}
	
	$output = $hash_func($opad.pack($pack, $hash_func($ipad.$data)));
	
	return ($raw_output) ? pack($pack, $output) : $output;
}

function hash_ipa($userRND, $widgetID, $password){
	return md5($userRND.'-'.$widgetID.'-'.$password);
}

function hash_api($userRND, $widgetID, $password){
	return hash_ipa($userRND, $widgetID, $password);
}

function file_hash(&$content){
	return md5($content);
}

function random_string($size, $chr_start=34, $chr_end=255){
	$cadena = '';
	for($i=0; $i<$size; ++$i){
		$cadena .= chr(mt_rand($chr_start, $chr_end));
	}
	return $cadena;
}

function truncate_filename($name, $max){
	if(strlen($name) > $max){
		if(strpos($name, '.') !== false){
			$dot      = strrpos($name, '.');
			$name_ext = substr($name, $dot +1);
			$name     = substr($name, 0, $max -1 -strlen($name_ext)).
						'.'.$name_ext;
		}
		else{
			$name     = substr($name, 0, $max);
		}
	}
	return $name;
}

function insert_nocache_headers(){
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
}



// To make benchmarks. Receives a function and returns the time it takes to execute the function:
/*
echo test(function(){for($i=0;$i<100000; ++$i){
	// Hacer algo 100.000 veces
}});
*/
function test($funcion){
	$time_start = microtime(true);
	$funcion();
	return number_format(microtime(true) - $time_start, 3);
}

// Must be called before any echo to be able to output headers
// 'db' | 'session'
function open_db_session($to_return = 'db'){
	require_once __DIR__.'/../config.php';
	require_once __DIR__.'/../lib/DB.php';
	require_once __DIR__.'/../lib/zebra_session/Zebra_Session.php';
	
	if(substr_count($_SERVER['SERVER_NAME'], '.') > 1){
		$domain = substr($_SERVER['SERVER_NAME'], strpos($_SERVER['SERVER_NAME'], '.'));
	} else {
		$domain = '.' .$_SERVER['SERVER_NAME'];
	}
	ini_set('session.cookie_domain', $domain);
	
	$db = new DB();
	$db->Open();

    $session = new Zebra_Session($db->mysqli, PASSWORD_ZEBRA_SESSION, ZEBRA_SESSION_TIME, true, true);
	
	if(isset($_COOKIE['PHPSESSID'])){
		setcookie('PHPSESSID', $_COOKIE['PHPSESSID'], time() + ZEBRA_SESSION_TIME, '/', $domain);
	}
	
	if(!isset($_SESSION['user'])){
		// Anonymous
		$user = $db -> check_nick_password(DEFAULT_USER_NICK, DEFAULT_USER_PASSWORD);
		$user['valid'] = false; //Anonymous user has valid = false
		$_SESSION['user'] = $user;
	}
	
	return $$to_return;
}

function user_check_access($allow_default_user = false){
	if(!$allow_default_user && !$_SESSION['user']['valid']){
		header('Location: //'.WEB_PATH, true, 302);
		exit;
	}
}

function send_mail($for, $subject, $body){
	$extra_headers = "MIME-Version: 1.0\r\n"
					."Content-type: text/html; charset=UTF-8\r\n"
					."To: {$for} <{$for}>\r\n"
					.'From: CoolStart.net <' . SMTP_EMAIL . ">\r\n"
					.'Reply-To: ' . SMTP_EMAIL . "\r\n"
					.'X-Mailer: PHP/' . phpversion();
	
	mail($for, $subject, $body, $extra_headers);
}

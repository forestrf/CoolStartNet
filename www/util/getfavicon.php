<?php
require_once __DIR__.'/../php/defaults.php';
require_once __DIR__.'/../php/config.php';

// Validate GET option

if (isset($_REQUEST['domain'])) {
	$domain = &$_REQUEST['domain'];
} else {
	fin_default();
}



// check login

require_once __DIR__.'/../php/functions/generic.php';

$db = open_db_session();

if(!user_check_access(false, true)){
	fin_default();
}


$html = CargaWebCurl('http://' . $domain);

$favicon = parseFavicon($html);
if ($favicon !== null) {
	if (preg_match('@^(?:https?:)?//@', $favicon)) {
		$faviconURL = $favicon;
	} else {
		$faviconURL = $domain . $favicon;
	}
} else {
	fin_default();
}

//echo $faviconURL;exit;
header("HTTP/1.1 301 Moved Permanently");
header('Location: //' . $faviconURL);



function fin_default() {
	header('Location: //' . WEB_PATH . 'img/favicon/favicon-generic-32.png');
	exit;
}

function CargaWebCurl($url){
	$cabeceras = array();
	$cabeceras[] = 'Accept-Encoding: gzip';
	$cabeceras[] = 'Connection: Connection';
	$cabeceras[] = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0';
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_COOKIEFILE, ""); // Esto es para que en las redirecciones use las cookies que salgan durante las redirecciones
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $cabeceras);
	
	// https
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
	// auto decoding
	curl_setopt($ch, CURLOPT_ENCODING, '');
	
	$t = curl_exec($ch);
	
	return $t;
}

function parseFavicon($html) {
	$s = '[ \t\r\n]*?';
	
	// Get the 'href' attribute value in a <link rel="icon" ... />
	// Also works for IE style: <link rel="shortcut icon" href="http://www.example.com/myicon.ico" />
	// And for iOS style: <link rel="apple-touch-icon" href="somepath/image.ico">
	$matches = array();
	// Search for <link rel="icon" type="image/png" href="http://example.com/icon.png" />
	;
	if (preg_match("/<link{$s}rel{$s}={$s}[\"'](?:shortcut )?icon[\"']{$s}href{$s}={$s}[\"']([^\"']*)[\"']/i", $html, $matches)) {
		return trim($matches[1]);
	}
	// Order of attributes could be swapped around: <link type="image/png" href="http://example.com/icon.png" rel="icon" />
	
	if (preg_match("/<link{$s}href{$s}={$s}[\"']([^\"']*)[\"']{$s}rel{$s}={$s}[\"'](?:shortcut )?icon[\"']/i", $html, $matches)) {
		return trim($matches[1]);
	}
	
	if (preg_match("/<meta{$s}content{$s}={$s}[\"']([^\"']*)[\"']{$s}itemprop{$s}={$s}[\"']image[\"']/i", $html, $matches)) {
		return trim($matches[1]);
	}
	// No match
	return null;
} 

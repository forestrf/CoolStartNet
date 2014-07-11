<?php

function isInteger($input){
    return ctype_digit(strval($input));
}

function custom_hmac($algo='md5', $data, $key, $raw_output=false){
	$algo = strtolower($algo);
	$pack = 'H'.strlen($algo('test'));
	$size = 64;
	$opad = str_repeat(chr(0x5C), $size);
	$ipad = str_repeat(chr(0x36), $size);
	
	if(strlen($key) > $size){
		$key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
	}
	else{
		$key = str_pad($key, $size, chr(0x00));
	}
	
	for($i = 0; $i < strlen($key) - 1; $i++){
		$opad[$i] = $opad[$i] ^ $key[$i];
		$ipad[$i] = $ipad[$i] ^ $key[$i];
	}
	
	$output = $algo($opad.pack($pack, $algo($ipad.$data)));
	
	return ($raw_output) ? pack($pack, $output) : $output;
}

function hash_ipa($usuarioRND, $widgetID, $password){
	return md5($usuarioRND.'-'.$widgetID.'-'.$password);
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



// Para pruebas de rendimiento. Se le pasa una función y retorna el tiempo que tarda en ejecutarse:
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
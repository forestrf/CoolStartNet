<?php

function isInteger($input) {
    return ctype_digit(strval($input));
}

function custom_hmac($data, $key, $hash_func='md5', $raw_output = false) {
	$hash_func = strtolower($hash_func);
	$pack = 'H'.strlen($hash_func('test'));
	$size = 64;
	$opad = str_repeat(chr(0x5C), $size);
	$ipad = str_repeat(chr(0x36), $size);
	
	if (strlen($key) > $size)
		$key = str_pad(pack($pack, $hash_func($key)), $size, chr(0x00));
	else
		$key = str_pad($key, $size, chr(0x00));
	
	
	for ($i = 0, $i_t = strlen($key) - 1; $i < $i_t; $i++) {
		$opad[$i] = $opad[$i] ^ $key[$i];
		$ipad[$i] = $ipad[$i] ^ $key[$i];
	}
	
	$output = $hash_func($opad.pack($pack, $hash_func($ipad.$data)));
	
	return ($raw_output) ? pack($pack, $output) : $output;
}

function hash_ipa($userRND, $widgetID, $password) {
	return md5($userRND.'-'.$widgetID.'-'.$password);
}

function hash_api($userRND, $widgetID, $password) {
	return hash_ipa($userRND, $widgetID, $password);
}

function file_hash(&$content) {
	return md5($content);
}

// if $chr_start is a string, use it as the available chars
function random_string($size, $chr_start=34, $chr_end=255) {
	$cadena = '';
	if (is_int($chr_start)) {
		for ($i=0; $i<$size; ++$i) {
			$cadena .= chr(mt_rand($chr_start, $chr_end));
		}
	} else {
		$f = strlen($chr_start);
		for ($i=0; $i<$size; ++$i) {
			$cadena .= $chr_start[mt_rand(0, $f)];
		}
	}
	return $cadena;
}

function truncate_filename($name, $max) {
	if (strlen($name) > $max) {
		if (strpos($name, '.') !== false) {
			$dot      = strrpos($name, '.');
			$name_ext = substr($name, $dot +1);
			$name     = substr($name, 0, $max -1 -strlen($name_ext)) . '.' . $name_ext;
		} else {
			$name     = substr($name, 0, $max);
		}
	}
	return $name;
}

function insert_nocache_headers() {
	header('Expires: Tue, 03 Jul 2001 06:00:00 GMT');
	header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
	header("Pragma: no-cache");
}


class G {
	/**
	 * @var Session
	 */
	public static $SESSION;
	public static $abcABC09 = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	public static $mimetype_extensions = array(
		'video/x-ms-asf' => array('asf', 'asr', 'asx'),
		'video/x-msvideo' => array('avi'),
		'application/octet-stream' => array('bin'),
		'image/bmp' => array('bmp'),
		'application/x-bzip' => array('bz'),
		'application/x-bzip2' => array('bz2'),
		'text/css' => array('css'),
		'image/gif' => array('gif'),
		'application/x-gzip' => array('gz', 'gzip'),
		'text/html' => array('htm', 'html'),
		'image/x-icon' => array('ico'),
		'image/jpeg' => array('jpe', 'jpeg', 'jpg'),
		'application/javascript' => array('js'),
		'audio/x-mpegurl' => array('m3u'),
		'audio/x-mid' => array('mid', 'midi'),
		'video/quicktime' => array('mov'),
		'video/x-sgi-movie' => array('movie'),
		'video/mpeg' => array('mp2', 'mpe', 'mpeg', 'mpg', 'mpv2', 'm1v', 'm2v'),
		'audio/mpeg' => array('mp3', 'm2a', 'mp2', 'mpa'),
		'application/pdf' => array('pdf'),
		'image/png' => array('png'),
		'application/vnd.ms-powerpoint' => array('pps', 'ppt'),
		'application/x-mspublisher' => array('pub'),
		'application/rtf' => array('rtf'),
		'application/smil' => array('smi', 'smil'),
		'image/svg+xml' => array('svg'),
		'application/x-shockwave-flash' => array('swf'),
		'application/x-tar' => array('tar'),
		'application/x-compressed' => array('tgz'),
		'image/tiff' => array('tif', 'tiff'),
		'text/plain' => array('txt', 'conf'),
		'image/svg+xml' => array('svg'),
		'application/x-font-ttf' => array('ttf'),
		'application/x-font-opentype' => array('otf'),
		'application/font-woff' => array('woff'),
		'application/vnd.ms-fontobject' => array('eot'),
		'text/x-vcard' => array('vcf'),
		'audio/x-wav' => array('wav'),
		'application/vnd.ms-excel' => array('xls', 'xlsx'),
		'application/msword' => array('doc', 'docx'),
		'application/x-compress' => array('z'),
		'application/zip' => array('zip'),
		'application/octet-stream' => array('')
	);
}
// Must be called before any echo to be able to output headers
// 'db' | 'session'
function open_db_session() {
	require_once __DIR__.'/../config.php';
	require_once __DIR__.'/../lib/DB.php';
	require_once __DIR__.'/../lib/Session/Session.php';
	
	$db = new DB();
	G::$SESSION = new Session($db);
	
	if (!G::$SESSION->exists()) {
		// Anonymous
		$user = $db -> check_nick_password(DEFAULT_USER_NICK, DEFAULT_USER_PASSWORD);
		G::$SESSION->userID = $user['IDuser'];
	}
	$db->set_user_id(G::$SESSION->userID);
	
	return $db;
}

function user_check_access($allow_default = false, $custom_error_action = false) {
	if (G::$SESSION->exists() || $allow_default) {
		return true;
	}

	if (!$custom_error_action) {
		header('Location: //'.WEB_PATH, true, 302);
		exit;
	}
	return false;
}

function send_mail($for, $subject, $body) {
	$extra_headers = "MIME-Version: 1.0\r\n"
					."Content-type: text/html; charset=UTF-8\r\n"
					."To: {$for} <{$for}>\r\n"
					.'From: CoolStart.net <' . MAIL_DIRECTION . ">\r\n"
					.'Reply-To: ' . MAIL_DIRECTION . "\r\n"
					.'X-Mailer: PHP/' . phpversion();
	
	mail($for, $subject, $body, $extra_headers);
}

function server_vars_js() {
	return '{
		"CAPTCHA_PUB_KEY": "'.CAPTCHA_PUBLIC_KEY.'",
		"WEB_PATH": "'.WEB_PATH.'"
	}';
}

function file_mimetype($filename) {
	$pos = strrpos($filename, '.');
	if ($pos === false) {
		return 'text/html';
	}
	$extension = substr($filename, $pos + 1);
	
	foreach(G::$mimetype_extensions as $mimetype => $extensions) {
		if (in_array($extension, $extensions)) {
			return $mimetype;
		}
	}
	
	return 'application/octet-stream';
}



function filter_directory(&$directory_resource, $show_folders = true, $show_files = true) {
	if (false !== $entry = $directory_resource->read()) {
		if ($entry === '.' || $entry === '..')
			return filter_directory($directory_resource, $show_folders, $show_files);
		else if ($show_folders && is_dir($directory_resource->path . $entry) || $show_files && is_file($directory_resource->path . $entry))
			return $entry;
	}
	return false;
}

function isset_and_default(&$array, $param, $default) {
	return isset($array[$param]) && $array[$param] !== '' ? $array[$param] : $default;
}

function file_upload_widget(DB &$db, $widgetID, &$FILE_REFERENCE, $name = NULL){
	if($FILE_REFERENCE['size'] <= MAX_FILE_SIZE_BYTES){
		$content = file_get_contents($FILE_REFERENCE['tmp_name']);
		
		// Innecesario borrarlo, php lo borra automaticamente.
		unlink($FILE_REFERENCE['tmp_name']);
		
		if ($name === NULL) {
			$name = truncate_filename($FILE_REFERENCE['name'], FILENAME_MAX_LENGTH);
		}
		$mimetype = $FILE_REFERENCE['type'];
		
		$db->upload_widget_file($widgetID, $name, file_mimetype($FILE_REFERENCE['name']), $content);
	}
}

function filter_nick($value) {
	return strlen($value) <= NICK_MAX_LENGTH;
}
function filter_password($value) {
	return strlen($value) <= PASSWORD_MAX_LENGTH && strlen($value) >= PASSWORD_MIN_LENGTH;
}
function filter_email($value) {
	return strlen($value) <= EMAIL_MAX_LENGTH && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
}
function filter_validation($value) {
	return strlen($value) <= 5;
}

<?php

class Session {
	
	var $userID = null;
	private $logged_in = false;
	
	private $user_random;
	private $db;
	
	// If a valid session exists, regenerate it extend its time
	// return true if there is a session opened, false otherwise
	function Session(DB &$db) {
		$this->db = $db;
		// Session cookie
		if (isset($_COOKIE['session'])) {
			if ($this->validate_session_string($_COOKIE['session'])) {
				// user remembered. regenerate session
				$this->create_session($this->userID);
			} else {
				// invalid session, delete it
				$this->remove_session();
			}
		}
	}
	
	// Start a session
	function create_session($userID) {
		$this->userID = (int)$userID;
		$this->user_random = $this->db->get_user_random($this->userID);
		$session_string = $this->generate_session_string($this->userID, $this->user_random);
		setcookie('session', $session_string, time() + SESSION_TIME, '/', DOMAIN, false, true);
		$this->logged_in = true;
	}
	
	// Remove the current (and only) session
	function remove_session() {
		setcookie('session', '', time() - 3600, '/', DOMAIN, false, true);
		$this->userID = null;
		$this->logged_in = false;
	}
	
	// is there a session?
	function exists() {
		return $this->logged_in;
	}
	
	// This must NO be printed never ever. A leak can be solved changing all the user's random strings and emailing a password reset form to all the users.
	function get_user_random() {
		return $this->user_random;
	}
	
	
	
	// userID|IP|YYYY-MM-DD HH:MM:SS|hmac(userID|IP|YYYY-MM-DD HH:MM:SS, user_random)
	private function generate_session_string($userID, $user_random, $time = null) {
		if ($time === null) {
			$time = Date('Y-m-d H:i:s', time() + SESSION_TIME);
		}
		$str = $userID . '|' . $_SERVER['REMOTE_ADDR'] . '|' . $time;
		$hmac = $this->custom_hmac($str, $user_random);
		return $str . '|' . $hmac;
	}
	
	// is the session string valid? also, extract and save the data if it is valid
	private function validate_session_string($session_string) {
		if (substr_count($session_string, '|') === 3) {
			$session_data = explode('|', $_COOKIE['session']);
			$user_random = $this->db->get_user_random($session_data[0]);
			
			// is the session time greater than today?
			if (strtotime($session_data[2]) > new DateTime()) {
				// is the user IP the same as the session IP?
				if ($_SERVER['REMOTE_ADDR'] === $session_data[1]) {
					$session_test = $this->generate_session_string($session_data[0], $user_random, $session_data[2]);
					
					// is the session really ok?
					if ($session_test === $session_string) {
						$this->userID = (int)$session_data[0];
						$this->user_random = $user_random;
						return true;
					}
				}
			}
		}
		return false;
	}
	
	
	
	// Generic functions
	
	private function custom_hmac($data, $key, $hash_func='md5', $raw_output = false) {
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
}

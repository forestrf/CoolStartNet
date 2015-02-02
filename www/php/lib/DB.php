<?php

# This file is part of CoolStart.net.
#
#	 CoolStart.net is free software: you can redistribute it and/or modify
#	 it under the terms of the GNU Affero General Public License as published by
#	 the Free Software Foundation, either version 3 of the License, or
#	 (at your option) any later version.
#
#	 CoolStart.net is distributed in the hope that it will be useful,
#	 but WITHOUT ANY WARRANTY; without even the implied warranty of
#	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#	 GNU Affero General Public License for more details.
#
#	 You should have received a copy of the GNU Affero General Public License
#	 along with CoolStart.net.  If not, see <http://www.gnu.org/licenses/>.

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../functions/generic.php';

// All the queries to the database are here. Change the database engine or the queries only have to be done here.
class DB {
	
	// global constants
	const GLOBAL_WIDGET  = '-1';
	
	
	
	// Login data to access the database. change in the config file.
	private $host = MYSQL_HOST;
	private $user = MYSQL_USER;
	private $pass = MYSQL_PASSWORD;
	private $bd   = MYSQL_DATABASE;
	
	var $mysqli;
	
	private $opened_connection = false;
	
	// Auto inserted id number
	var $LAST_MYSQL_ID = '';
	
	function Open($host=null, $user=null, $pass=null, $bd=null) {
		if($this->d) $this->debug('Opening database');
		if ($host !== null)
			$this->host = $host;
		if ($user !== null)
			$this->user = $user;
		if ($pass !== null)
			$this->pass = $pass;
		if ($bd !== null)
			$this->bd = $bd;
			
		// Persistent connection:
		// http://www.php.net/manual/en/mysqli.persistconns.php
		// To open a persistent connection you must prepend p: to the hostname when connecting. 
		$this->mysqli = new mysqli('p:'.$this->host, $this->user, $this->pass, $this->bd);
		if ($this->mysqli->connect_errno) {
			if($this->d) $this->debug('Failed to connect to MySQL: (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error);
			$this->away = true;
			return false;
		}
		$this->away = false;
		$this->mysqli->set_charset('utf8');

		if($this->d) $this->debug('Database opened');
		return true;
	}
	
	// Make a SQL query. Returns false if there is an error, and throws an exception.
	// Queries are only done here. This way a connection can be opened if necessary or can be used a cached response.
	// $this->LAST_MYSQL_ID stores the ID of the last insert query
	private function query($query, $cacheable = false) {
		if ($cacheable) {
			$cached = $this->queryCache($query);
			if ($cached !== false) {
				if($this->d) $this->debug('cached result returned for: '.$query);
				return $cached;
			}
		}
		
		if ($this->opened_connection === false) {
			if (!$this->Open()) {
				if($this->d) $this->debug('Can\'t open the database');
				return false;
			}
			$this->opened_connection = true;
		}
		
		$result = $this->mysqli->query($query, MYSQLI_USE_RESULT);
		if (strpos($query, 'INSERT') !== false) {
			$this->LAST_MYSQL_ID = $this->mysqli->insert_id;
		} else {
			$this->LAST_MYSQL_ID = null;
		}
		if ($result === false || $result === true) {
			if ($cacheable) $this->cacheResult($query, $resultArray);
			if($this->d) $this->debug('<span class="info">query</span>: <span class="query">'.$query."</span>\r\n<span class='info'>result</span>: <b class=\"".($result?'ok">TRUE':'fail">FALSE ('.$this->mysqli->error.')')."</b>\r\n");
			return $result;
		}
		
		$resultArray = array();
		while ($rt = $result->fetch_array(MYSQLI_ASSOC)) $resultArray[] = $rt;
		if ($cacheable) {
			$this->cacheResult($query, $resultArray);
		}
		if($this->d) $this->debug('<span class="info">query</span>: <span class="query">'.$query."</span>\r\n<span class='info'>result</span>: ".print_r($resultArray)."\r\n");
		return $resultArray;
	}
	
	// Variables
	private $userID;
	private $away = false;
	
	function set_user_id($userID) {
		$this->userID = $userID;
	}
	function get_user_id() {
		return $this->userID;
	}
	
	function is_away() {
		return $this->away;
	}
	
	//debug mode
	var $debug_array = array();
	private $d = false;
	private $d_array = false;
	function debug_mode($bool) {
		$this->d = $bool;
	}
	function debug_to_array($bool) {
		$this->d_array = $bool;
	}
	private function debug($txt) {
		if ($this->d) {
			if ($this->d_array) {
				$this->debug_array[] = $txt;
			} else {
				echo $txt . "\r\n";
			}
		}
	}
	
	
	
	// Not the best option
	function create_tables(&$content) {
		//remove comments
		$instructions = preg_replace('/--.*?[\r\n]/', '', $content);
		$instructions = preg_replace('|/\*.*?\*/|', '', $instructions);
		$instructions = str_replace("\n", '', $instructions);
		$instructions = str_replace("\r", '', $instructions);
		$instructions = explode(";", $instructions);
		foreach ($instructions as $instruction)
			if ($instruction !== '')
				$this->query($instruction);
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# USERS
	#
	# ---------------------------------------------------------------------------
	
	/*
	levels:
	0 => account not validated
	200 => normal user
	1000 => lol admin
	*/
	
	// Check if exists a nick.
	function nick_exists($nick) {
		$nick = mysql_escape_mimic($nick);
		return count($this->query("SELECT * FROM `users` WHERE `nick` = '{$nick}';", true)) > 0;
	}
	
	function get_random_from_user_nick($nick) {
		$nick = mysql_escape_mimic($nick);
		$result = $this->query("SELECT `RND` FROM `users` WHERE `nick` = '{$nick}';");
		return count($result) === 1 ? $result[0]['RND'] : '';
	}
	
	function get_user_random($userID) {
		$userID = mysql_escape_mimic($userID);
		$result = $this->query("SELECT `RND` FROM `users` WHERE `IDuser` = '{$userID}';", true);
		return count($result) > 0 ? $result[0]['RND'] : '';
	}
	
	// Insert a new user. Data previously validated and sanitized
	function create_new_user($nick, $password, $email, &$validation) {
		$nick = mysql_escape_mimic($nick);
		$email = mysql_escape_mimic($email);
		$rnd = utf8_encode(random_string(32));
		$password = hash_password($password, $rnd);
		$rnd = mysql_escape_mimic($rnd);
		$validation = mysql_escape_mimic(utf8_encode(random_string(5, G::$abcABC09)));
		return $this->query("INSERT INTO `users` (`nick`, `password`, `email`, `RND`, `validation`, `creation_date`) VALUES ('{$nick}', '{$password}', '{$email}', '{$rnd}', '{$validation}', NOW());") === true;
	}
	
	// Insert a new user. Data previously validated and sanitized. Returns false or the user email
	// Remove the validation of the new user. Validation will only be used now to set a new password when recovering the account
	function validate_new_user($nick, $validation) {
		$nick = mysql_escape_mimic($nick);
		$validation = mysql_escape_mimic($validation);
		if ($this->query("UPDATE `users` set `level` = 200, `validation` = '' WHERE `level` = 0 AND `nick` = '{$nick}' AND `validation` = '{$validation}';") === true) {
			$email = $this->query("SELECT `email` FROM `users` WHERE `nick` = '{$nick}';");
			return $email[0]['email'];
		}
		return false;
	}
	
	// pre-change a users password. Data previously validated and sanitized. Returns bool
	function recover_account($email, &$nick, &$validation) {
		$email = mysql_escape_mimic($email);
		$resp = $this->query("SELECT `nick` FROM `users` WHERE `email` = '{$email}' AND `level` >= 200;");
		if ($resp) {
			$nick = $resp[0]['nick'];
			$validation = mysql_escape_mimic(utf8_encode(random_string(5, G::$abcABC09)));
			return $this->query("UPDATE `users` set `validation` = '{$validation}', `recover_code_due_date` = NOW() + INTERVAL 1 DAY WHERE `nick` = '{$nick}' AND `email` = '{$email}';") === true;
		}
		return false;
	}
	
	// pre-change a users password. Data previously validated and sanitized. Returns the email or false
	function recover_account_validate($nick, $validation) {
		$nick = mysql_escape_mimic($nick);
		$validation = mysql_escape_mimic($validation);
		$resp = $this->query("SELECT `email` FROM `users` WHERE `nick` = '{$nick}' AND `validation` = '{$validation}' AND `level` >= 200;");
		return $resp ? $resp[0]['email'] : false;
	}
	
	// Returns the user after validating the nick and the password or returns false if the user doesn't match.
	function check_nick_password($nick, $password) {
		$nick = mysql_escape_mimic($nick);
		$password = hash_password($password, $this->get_random_from_user_nick($nick));
		$result = $this->query("SELECT * FROM `users` WHERE `level` >= 200 && `nick` = '{$nick}' AND `password` = '{$password}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Returns the user after validating the userID and the password or returns false if the user doesn't match.
	function check_user_password($userID, $password) {
		$userID = mysql_escape_mimic($userID);
		$password = hash_password($password, $this->get_user_random($userID));
		$result = $this->query("SELECT * FROM `users` WHERE `level` >= 200 && `IDuser` = '{$userID}' AND `password` = '{$password}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Change the password of the user. Returns bool = success
	function modify_password($userID, $new_password) {
		$userID = mysql_escape_mimic($userID);
		$new_password = hash_password($new_password, $this->get_user_random($userID));
		return $this->query("UPDATE `users` SET `password` = '{$new_password}' WHERE `IDuser` = '{$userID}';");
	}

	// Remove user account
	function delete_user($nick) {
		$nick = mysql_escape_mimic($nick);
		return $this->query("DELETE FROM `users` WHERE `nick` = '{$nick}';");
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS
	#
	# ---------------------------------------------------------------------------
	
	// Returns the widget configuration given a widget name or false if the widget doesn't exists.
	function get_widget($name) {
		$name = mysql_escape_mimic($name);
		$result = $this->query("SELECT * FROM `widgets` WHERE `name` = '{$name}' LIMIT 1;");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Returns the widget configuration given a widget ID or false if the widget doesn't exists.
	function get_widget_by_ID($ID) {
		$ID = mysql_escape_mimic($ID);
		$result = $this->query("SELECT * FROM `widgets` WHERE `IDwidget` = '{$ID}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// array with widgets ID. Returns an array with the existent widgets ID.
	function exists_widgets(&$widgets) {
		$SQL_statement = array();
		foreach ($widgets as $widgetID) {
			$widgetID = mysql_escape_mimic($widgetID);
			$SQL_statement[] = " `IDwidget` = '{$widgetID}' ";
		}
		$result = $this->query("SELECT `IDwidget` FROM `widgets` WHERE ".implode('OR', $SQL_statement).";");
		$to_return = array();
		foreach ($result as $widget) {
			$to_return[] = $widget['IDwidget'];
		}
		return $to_return;
	}
	
	// Returns a variable of the user.
	function get_variable(&$widgets) {
		$SQL_statement = array();
		foreach ($widgets as $widgetID => &$variables) {
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Ignore $value
			foreach ($variables['keys'] as $variable => &$value) {
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "(`IDwidget` = '{$widgetID}' AND `variable` = '{$variable}')";
			}
		}
		
		return $this->query("SELECT `IDwidget`, `variable`, `value` FROM `variables` WHERE `IDuser` = '{$this->userID}' AND (".implode('OR', $SQL_statement).");");
	}
	
	// Returns if the variable exists.
	function check_variable(&$widgets) {
		$SQL_statement = array();
		foreach ($widgets as $widgetID => &$variables) {
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Ignore $value
			foreach ($variables['keys'] as $variable => &$value) {
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "(`IDwidget` = '{$widgetID}' AND `variable` = '{$variable}')";
			}
		}
		
		return $this->query("SELECT `IDwidget`, `variable` FROM `variables` WHERE `IDuser` = '{$this->userID}' AND (".implode('OR', $SQL_statement).");");
	}
	
	// Doesn't check if the widget exists. This check is done in api.php
	// Check if the user has space to save the new data
	function set_variable(&$widgets) {
		$occupied = $this->get_user_size_variable();
		
		$SQL_statement = array();
		foreach ($widgets as $widgetID => &$variables) {
			$widgetID = mysql_escape_mimic($widgetID);
			
			foreach ($variables['keys'] as $variable => &$value) {
				$value = json_encode($value);
				$occupied[$widgetID][$variable] = strlen($value);
				$value = mysql_escape_mimic($value);
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "('{$this->userID}', '{$widgetID}', '{$variable}', '{$value}')";
			}
		}
		
		if ($this->calculate_occupied_size_from_object($occupied) < USER_MAX_BYTES_STORED_DB) {
			return $this->query("INSERT INTO `variables` (`IDuser`, `IDwidget`, `variable`, `value`) VALUES ".implode(',', $SQL_statement)." ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);");
		}
		return false;
	}
	
	// Return an array with all the variables with its sizes from a user
	function get_user_size_variable() {
		$pre_occupied = $this->query("SELECT `IDwidget`, `variable`, LENGTH(`value`) AS `total_size` FROM `variables` WHERE `IDuser` = '{$this->userID}';");
		$occupied = array();
		for ($i = 0, $i_t = count($pre_occupied); $i < $i_t; $i++)
			$occupied[$pre_occupied[$i]['IDwidget']][$pre_occupied[$i]['variable']] = intval($pre_occupied[$i]['total_size']);
		
		return $occupied;
	}
	
	function calculate_occupied_size_from_object(&$object) {
		$totalSize = 0;
		foreach ($object as $IDwidget => &$variable)
			foreach ($variable as &$size)
				$totalSize += $size;
		
		return $totalSize;
	}
	
	function del_variable(&$widgets) {
		$SQL_statement = array();
		foreach ($widgets as $widgetID => &$variables) {
			$widgetID = mysql_escape_mimic($widgetID);
			
			foreach ($variables['keys'] as $variable => &$value) {
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "(`IDwidget` = '{$widgetID}' AND `variable` = '{$variable}')";
			}
		}
		
		return $this->query("DELETE FROM `variables` WHERE `IDuser` = '{$this->userID}' AND (".implode(' OR ', $SQL_statement).");");
	}
	
	function delall_variable(&$widgets, $private_only = true) {
		$SQL_statement = array();
		foreach ($widgets as $widgetID => &$variable_value) {
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			if ($private_only && $widgetID === 'global')
				continue;
			
			$SQL_statement[] = "`IDwidget` = '{$widgetID}'";
		}
		
		return $this->query("DELETE FROM `variables` WHERE `IDuser` = '{$this->userID}' AND (".implode(' OR ', $SQL_statement).");");
	}
	
	// Create a widget.
	function create_widget($name) {
		$name = mysql_escape_mimic($name);
		if (!$this->query("SELECT `IDwidget` FROM `widgets` WHERE `name` = '{$name}' LIMIT 1;"))
			return $this->query("INSERT INTO `widgets` (`name`, `ownerID`) VALUES ('{$name}', '{$this->userID}');");
		return false;
	}
	
	function set_widget_data($widgetID, $data_arr) {
		if ($this->CanIModifyWidget($widgetID)) {
			$sql = array();
			foreach ($data_arr as $name => $value) {
				$name = mysql_escape_mimic($name);
				if (is_array($value)) {
					$value = json_encode($value);
				} else {
					$value = mysql_escape_mimic($value);
				}
				$sql[] = "`{$name}` = '{$value}'";
			}
			return $this->query("UPDATE `widgets` SET " . implode(', ', $sql) . " WHERE `IDwidget` = '{$widgetID}';");
		}
		return false;
	}
	
	// Delete a widget given the ID of the widget.
	// Deleting a widget also deletes the widget variables of users and users lose it if they are using it.
	// Doesn't delete from the table 'files' because other file can has the same hash. The unlinked content is deleted from other function that is called from a cronjob.
	function delete_widget($widgetID) {
		if ($this->CanIModifyWidget($widgetID) && $this->query("SELECT * FROM `widgets` WHERE `IDwidget` = '{$widgetID}';")) {
			/*
			$this->query("DELETE FROM `widgets` WHERE `ID` = '{$widgetID}';");
			$this->query("DELETE FROM `variables` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}';");
			*/
			return $this->query("DELETE FROM `widgets` WHERE `IDwidget` = '{$widgetID}';" .
					"DELETE FROM `variables` WHERE `IDwidget` = '{$widgetID}';" .
					"DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}';" .
					"DELETE FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}';");
		}
		return false;
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS - FILES
	#
	# ---------------------------------------------------------------------------
	
	// Get a file of a widget given its name. The user needs to be the owner or be using the widget.
	function get_widget_file($widgetID, $name) {
		if ($this->check_using_widget_user($widgetID) || $this->CanIModifyWidget($widgetID)) {
			$name = mysql_escape_mimic($name);
			$result = $this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `name` = '{$name}';");
			return isset($result) ? $result[0] : false;
		}
		return false;
	}
	
	// Delete all unlinked files on the files table
	function delete_unlinked_files() {
		$this->query("DELETE FROM `files` WHERE `hash` NOT IN (SELECT `hash` FROM `widgets-content`);");
	}
	
	// Rename a file from a widget.
	function rename_widget_file($widgetID, $hash, $name) {
		if ($this->CanIModifyWidget($widgetID)) {
			$hash = mysql_escape_mimic($hash);
			$name = mysql_escape_mimic($name);
			return $this->query("UPDATE `widgets-content` SET `name` = '{$name}' WHERE `IDwidget` = '{$widgetID}' AND `hash` = '{$hash}';");
		}
		return false;
	}
	
	// Get the content files of a widget.
	function get_widget_contents($widgetID) {
		$widgetID = mysql_escape_mimic($widgetID);
		return $this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}';");
	}
	
	// Save a file for a widget with a name and a mimetype. Checks if the file can be uploaded to the version.
	function upload_widget_file($widgetID, $name, &$content, $flag_static = false) {
		if ($this->CanIModifyWidget($widgetID)) {
			if (count($this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}';")) >= WIDGET_MAX_FILES_NUMBER) {
				return false;
			}
			
			$name = mysql_escape_mimic($name);
			$hash = file_hash($content);
			$this->query("INSERT INTO `widgets-content` (`IDwidget`, `name`, `hash`) VALUES ('{$widgetID}', '{$name}', '{$hash}') ON DUPLICATE KEY UPDATE `hash` = VALUES(`hash`);");
			
			$filename = $this->get_widget_file_path_from_hash($hash);
			if (!is_file($filename)) {
				file_put_contents($filename, $content);
			}
			return true;
		}
		return false;
	}
	
	function get_widget_file_path_from_hash($hash) {
		return __DIR__.'/../../'.WIDGET_FILES_PATH.$hash;
	}
	
	// Delete a file from a widget.
	function delete_widget_file($widgetID, $hash) {
		if ($this->CanIModifyWidget($widgetID)) {
			$hash = mysql_escape_mimic($hash);
			return $this->query("DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `hash` = '{$hash}';");
		}
		return false;
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS - USERS
	#
	# ---------------------------------------------------------------------------
	
	// left = all widgets, right = only used widgets
	private function SELECT_FROM_WIDGETS_JOIN_WIDGETSUER($JOIN_TYPE = 'LEFT') {
		return "SELECT `widgets`.*, `widgets-user`.`IDuser` FROM `widgets` {$JOIN_TYPE} JOIN `widgets-user` ON (`widgets`.`IDwidget` = `widgets-user`.`IDwidget` AND `widgets-user`.`IDuser` = {$this->userID}) ";
	}
	
	// Returns a list with the widgets of the user.
	function get_widgets_user() {
		return $this->query($this->SELECT_FROM_WIDGETS_JOIN_WIDGETSUER('RIGHT') . "WHERE `IDuser` = '{$this->userID}';");
	}
	
	// Returns a widget of the user.
	function get_widget_user($widgetID) {
		$result = $this->query($this->SELECT_FROM_WIDGETS_JOIN_WIDGETSUER('RIGHT') . "WHERE `IDuser` = '{$this->userID}' AND `widgets`.`IDwidget` = '{$widgetID}';");
		return $result ? $result[0] : false;
	}
	
	// Returns a list with the widgets available to the user.
	function get_availabe_widgets_user() {
		return $this->query($this->SELECT_FROM_WIDGETS_JOIN_WIDGETSUER('LEFT') . "WHERE `widgets`.`IDwidget` != ".self::GLOBAL_WIDGET." AND (`ownerID` = '".GLOBAL_USER_ID."' OR `ownerID` = '{$this->userID}');"); // Por poner filtrado de widgets privados
	}
	
	// Returns a list with the widgets available to the user.
	function search_availabe_widgets_user($word) {
		return $this->query($this->SELECT_FROM_WIDGETS_JOIN_WIDGETSUER('LEFT') . "WHERE `widgets`.`IDwidget` != ".self::GLOBAL_WIDGET." AND (`ownerID` = '".GLOBAL_USER_ID."' OR `ownerID` = '{$this->userID}') AND (`name` LIKE '%{$word}%' OR `description` LIKE '%{$word}%' OR `fulldescription` LIKE '%{$word}%');"); // Por poner filtrado de widgets privados
	}
	
	// Returns a list with the widgets owned by the user.
	function get_widgets_user_owns() {
		return $this->query($this->SELECT_FROM_WIDGETS_JOIN_WIDGETSUER('LEFT') . "WHERE `ownerID` = '{$this->userID}';");
	}
	
	// Returns true if the widget can be manipulated by the user (true if user is the owner of te widget). Otherwise returns false.
	function CanIModifyWidget(&$widgetID) {
		$widgetID = mysql_escape_mimic($widgetID);
		return $this->query("SELECT * FROM `widgets` WHERE `IDwidget` = '{$widgetID}' AND `ownerID` = '{$this->userID}';") ? true : false;
	}
	
	// Makes the user stop using a widget without deleting the widget configurations.
	function remove_using_widget_user($widgetID) {
		$widgetID = mysql_escape_mimic($widgetID);
		$this->query("DELETE FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}' AND `IDuser` = '{$this->userID}';");
	}
	
	// Makes the user to use a widget. TO DO: Check if the user has access to the widget
	function add_using_widget_user($widgetID) {
		$widgetID = mysql_escape_mimic($widgetID);
		$this->query("INSERT INTO `widgets-user` (`IDwidget`, `IDuser`) VALUES ('{$widgetID}', '{$this->userID}');");
	}
	
	// Returns true if the user is using the widget, otherwise returns false.
	function check_using_widget_user(&$widgetID) {
		$widgetID = mysql_escape_mimic($widgetID);
		return $this->query("SELECT * FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}' AND `IDuser` = '{$this->userID}';") ? true : false;
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# USERS - DROPBOX
	#
	# ---------------------------------------------------------------------------
	
	// Returns all the tokens that the current user have
	function getAllAccessToken() {
		$resp = $this->query($this->query_getAllAccessToken(), true);
		return isset($resp[0]) ? $resp[0] : array();
	}
	private function query_getAllAccessToken() {return "SELECT `dropbox_accessToken` FROM `access-token` WHERE `IDuser` = '{$this->userID}';";}
	
	// Set the dropbox token of the current user
	function setDropboxAccessToken($accessToken) {
		deleteCache($this->query_getAllAccessToken());
		return $this->query("INSERT INTO `access-token` (`IDuser`, `dropbox_accessToken`) VALUES ('{$this->userID}', '{$accessToken}') ON DUPLICATE KEY UPDATE `dropbox_accessToken` = '{$accessToken}';");
	}
	
	// Delete the dropbox token of the current user
	function delDropboxAccessToken() {
		deleteCache($this->query_getAllAccessToken());
		return $this->query("UPDATE `access-token` SET `dropbox_accessToken` = '' WHERE `IDuser` = '{$this->userID}';");
	}
	
	
	
	// --------------------------------------------------------
	
	// (SET) Cache a result. $query is the sql to be cached, $result is the array of the response.
	function cacheResult($query, $result) {
		apc_store($query, $result, QUERY_CACHE_TTL);
	}
	
	// (GET) Cache a result. $query is the sql to search in the cache, $result is the array of the response.
	function queryCache($query) {
		return apc_fetch($query); // false if it fails
	}
	
	// (DEL) Cache a result. $query is the sql to delete from the cache.
	function deleteCache($query) {
		apc_delete($query);
	}
}

// Copy of mysql_real_escape_string to use it without an opened connection.
// http://es1.php.net/mysql_real_escape_string
function mysql_escape_mimic($inp) {
	if (is_array($inp))
		return array_map(__METHOD__, $inp);

	if (!empty($inp) && is_string($inp))
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);

	return $inp;
}

function hash_password($password, $salt) {
	return custom_hmac($password, $salt);
}

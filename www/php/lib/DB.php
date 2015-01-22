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
	const GLOBAL_VERSION = -1;
	const GLOBAL_WIDGET  = -1;
	
	
	
	// Login data to access the database. change in the config file.
	private $host = MYSQL_HOST;
	private $user = MYSQL_USER;
	private $pass = MYSQL_PASSWORD;
	private $bd   = MYSQL_DATABASE;
	
	var $mysqli;
	
	private $opened_connection = false;
	
	// Auto inserted id number
	var $LAST_MYSQL_ID = '';
	
	private $cache = array();
	
	function Open($host=null, $user=null, $pass=null, $bd=null) {
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
			// echo "Failed to connect to MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error;
			return false;
		}
		$this->mysqli->set_charset('utf8');

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
	
	function set_user_id($userID) {
		$this->userID = $userID;
	}
	
	
	//debug mode
	private $d = false;
	function debug_mode($bool) {
		$this->d = $bool;
	}
	private function debug($txt) {
		if ($this->d) echo $txt . "\r\n";
	}
	
	
	
	// Not the best option
	function create_tables(&$content) {
		//remove comments
		$instructions = preg_replace('/--.*?[\r\n]/', '', $content);
		$instructions = preg_replace('|/\*.*?\*/|', '', $instructions);
		$instructions = str_replace("\n", '', $instructions);
		$instructions = str_replace("\r", '', $instructions);
		//var_dump($instructions);
		$instructions = explode(";", $instructions);
		//var_dump($instructions);return;
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
		return count($this->query("SELECT * FROM `users` WHERE `nick` = '{$nick}'", true)) > 0;
	}
	
	function get_random_from_user_nick($nick) {
		$result = $this->query("SELECT `RND` FROM `users` WHERE `nick` = '{$nick}';");
		return count($result) === 1 ? $result[0]['RND'] : '';
	}
	
	// Insert a new user. Data previously validated and sanitized
	function create_new_user($nick, $password, $email, &$validation) {
		require_once __DIR__ . '/../functions/generic.php';
		$nick = mysql_escape_mimic($nick);
		$email = mysql_escape_mimic($email);
		$rnd = mysql_escape_mimic(utf8_encode(random_string(32)));
		$password = hash_password($password, $rnd);
		$validation = mysql_escape_mimic(utf8_encode(random_string(5)));
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
		require_once __DIR__ . '/../functions/generic.php';
		$email = mysql_escape_mimic($email);
		$resp = $this->query("SELECT `nick` FROM `users` WHERE `email` = '{$email}' AND `level` >= 200;");
		if ($resp) {
			$nick = $resp[0]['nick'];
			$validation = mysql_escape_mimic(utf8_encode(random_string(5)));
			return $this->query("UPDATE `users` set `validation` = '{$validation}', `recover_code_due_date` = NOW() + INTERVAL 1 DAY WHERE `nick` = '{$nick}' AND `email` = '{$email}';") === true;
		}
		return false;
	}
	
	// pre-change a users password. Data previously validated and sanitized. Returns the email or false
	function recover_account_validate($nick, $validation) {
		require_once __DIR__ . '/../functions/generic.php';
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
	
	// Change the password of the user. Returns bool = success
	function modify_password($nick, $new_password) {
		$nick = mysql_escape_mimic($nick);
		$new_password = hash_password($new_password, $this->get_random_from_user_nick($nick));
		return $this->query("UPDATE `users` SET `password` = '{$new_password}' WHERE `nick` = '{$nick}';");
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
		$result = $this->query("SELECT * FROM `widgets` WHERE `name` = '{$name}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Returns the widget configuration given a widget ID or false if the widget doesn't exists.
	function get_widget_by_ID($ID) {
		$ID = mysql_escape_mimic($ID);
		$result = $this->query("SELECT * FROM `widgets` WHERE `IDwidget` = '{$ID}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// global is a invisible widget with id -1
	function calc_widgetID($widgetID_mixed) {
		if ($widgetID_mixed === 'global' || strpos($widgetID_mixed, ''.self::GLOBAL_WIDGET) === 0)
			return self::GLOBAL_WIDGET;
		else if (strpos($widgetID_mixed, '-') !== false)
			return substr($widgetID_mixed, 0, strpos($widgetID_mixed, '-'));
		else
			return $widgetID_mixed;
	}
	
	// Returns a variable of the user.
	// $widgetID_variable must be an array that follows the next pattern:
	/*
		array(
			'widgetID' => array(
				'variable' => '', ...
			), ...
		);
	*/
	function get_variable(&$widgetID_variable) {
		$SQL_statement = array();
		foreach ($widgetID_variable as $widgetID => &$variables) {
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			$widgetID_calc = $this->calc_widgetID($widgetID);
			
			// Ignore $value
			foreach ($variables as $variable => &$value) {
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "(`IDwidget` = '{$widgetID_calc}' AND `variable` = '{$variable}')";
			}
		}
		
		return $this->query("SELECT `IDwidget`, `variable`, `value` FROM `variables` WHERE `IDuser` = '{$this->userID}' AND (".implode('OR', $SQL_statement).");");
	}
	
	
	function check_variable(&$widgetID_variable) {
		$SQL_statement = array();
		foreach ($widgetID_variable as $widgetID => &$variables) {
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			$widgetID_calc = $this->calc_widgetID($widgetID);
			
			// Ignore $value
			foreach ($variables as $variable => &$value) {
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "(`IDwidget` = '{$widgetID_calc}' AND `variable` = '{$variable}')";
			}
		}
		
		return $this->query("SELECT `IDwidget`, `variable` FROM `variables` WHERE `IDuser` = '{$this->userID}' AND (".implode('OR', $SQL_statement).");");
	}
	
	// Doesn't check if the widget exists. This check is done in api.php
	// Check if the user has space to save the new data
	// $widgetID_variable_value must be an array that follows the next pattern:
	/*
		array(
			'widgetID' => array(
				'variable' => 'value', ...
			), ...
		);
	*/
	function set_variable(&$widgetID_variable_value) {
		$occupied = $this->get_user_size_variable();
		
		$SQL_statement = array();
		foreach ($widgetID_variable_value as $widgetID => &$variable_value) {
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			$widgetID_calc = $this->calc_widgetID($widgetID);
			
			foreach ($variable_value as $variable => &$value) {
				$value = json_encode($value);
				$occupied[$widgetID_calc][$variable] = strlen($value);
				$value = mysql_escape_mimic($value);
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "('{$this->userID}', '{$widgetID_calc}', '{$variable}', '{$value}')";
			}
		}
		
		if ($this->calculate_occupied_size_from_object($occupied) < USER_MAX_BYTES_STORED_DB) {
			return $this->query("INSERT INTO `variables` (`IDuser`, `IDwidget`, `variable`, `value`) VALUES ".implode(',', $SQL_statement)." ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);");
		}
		return false;
	}
	
	
	/*
	array(
		'IDwidget' => array(
			'variable' => (int)size, ...
		), ...
	)
	*/
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
	
	function del_variable(&$widgetID_variable_value) {
		$SQL_statement = array();
		foreach ($widgetID_variable_value as $widgetID => &$variable_value) {
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			$widgetID_calc = $this->calc_widgetID($widgetID);
			
			foreach ($variable_value as $variable => &$value) {
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "(`IDwidget` = '{$widgetID_calc}' AND `variable` = '{$variable}')";
			}
		}
		
		return $this->query("DELETE FROM `variables` WHERE `IDuser` = '{$this->userID}' AND (".implode(' OR ', $SQL_statement).");");
	}
	
	function delall_variable(&$widgetID_variable_value, $private_only = true) {
		$SQL_statement = array();
		foreach ($widgetID_variable_value as $widgetID => &$variable_value) {
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			if ($private_only && $widgetID === 'global')
				continue;
			
			$widgetID_calc = $this->calc_widgetID($widgetID);
			
			$SQL_statement[] = "`IDwidget` = '{$widgetID_calc}'";
		}
		
		return $this->query("DELETE FROM `variables` WHERE `IDuser` = '{$this->userID}' AND (".implode(' OR ', $SQL_statement).");");
	}
	
	// Create a widget.
	function create_widget($name) {
		$name = mysql_escape_mimic($name);
		if (!$this->query("SELECT `IDwidget` FROM `widgets` WHERE `name` = '{$name}';"))
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
		if ($this->CanIModifyWidget($widgetID) && $this->query("SELECT * FROM `widgets` WHERE `IDwidget` = '{$widgetID}' AND `published` = '-1';")) {
			/*
			$this->query("DELETE FROM `widgets` WHERE `ID` = '{$widgetID}';");
			$this->query("DELETE FROM `variables` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}';");
			*/
			return $this->query("DELETE FROM `widgets` WHERE `IDwidget` = '{$widgetID}';" .
					"DELETE FROM `variables` WHERE `IDwidget` = '{$widgetID}';" .
					"DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}';" .
					"DELETE FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}';" .
					"DELETE FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}';");
		}
		return false;
	}
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS - VERSIONS
	#
	# ---------------------------------------------------------------------------
	
	// Set a public version as visible or invisible. $visible = true o false.
	function set_widget_version_visibility($widgetID, $version, $visible) {
		if ($this->CanIModifyWidget($widgetID) && can_be_widget_version($version)) {
			$visible = $visible ? 1 : 0;
			return $this->query("UPDATE `widgets-versions` SET `visible` = '{$visible}' WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `version` = '{$version}';");
		}
		return false;
	}
	
	// Set all public widget versions as invisible.
	function hide_all_widget_versions($widgetID) {
		if ($this->CanIModifyWidget($widgetID))
			return $this->query("UPDATE `widgets-versions` SET `visible` = '0' WHERE `IDwidget` = '{$widgetID}';");
		return false;
	}
	
	// Set a comment for the widget version.
	function set_widget_comment($widgetID, $version, $comment) {
		if ($this->CanIModifyWidget($widgetID) && can_be_widget_version($version)) {
			$comment = mysql_escape_mimic($comment);
			return $this->query("UPDATE `widgets-versions` SET `comment` = '{$comment}' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
		}
		return false;
	}
	
	// Delete a private widget version.
	function delete_private_widget_version($widgetID, $version) {
		if (can_be_widget_version($version) && $this->CanIModifyWidget($widgetID) && $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `public` = '0';")) {
			/*
			$this->query("DELETE FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			$this->query("DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			*/
			$this->query("DELETE FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';" .
					"DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			return true;
		}
		return false;
	}
	
	// Set a public widget version as the default public widget version.
	function set_widget_default_version($widgetID, $version) {
		if ($this->CanIModifyWidget($widgetID) &&
				can_be_widget_version($version) &&
				// Check if the version exists and is public
				$this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `version` = '{$version}';"))
			return $this->query("UPDATE `widgets` SET `published` = '{$version}' WHERE `IDwidget` = '{$widgetID}';");
		return false;
	}
	
	// Create a version of the widget.
	function create_widget_version($widgetID, &$new_version) {
		if (!$this->CanIModifyWidget($widgetID))
			return false;
		
		$new_version = $this->get_widget_last_version($widgetID, false);
		$new_version = $new_version['version'];
		if (!$new_version)
			$new_version = 0;
		
		++$new_version;
		return $this->query("INSERT INTO `widgets-versions` (`IDwidget`, `version`) VALUES ('{$widgetID}', '{$new_version}');");
	}
	
	// Publicate a widget version (cannot be undone).
	function publicate_widget_version($widgetID, $version) {
		if ($this->CanIModifyWidget($widgetID) && can_be_widget_version($version) &&
		$this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';")) {
			$this->query("UPDATE `widgets-versions` SET `public` = '1' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			if ($this->query("SELECT * FROM `widgets` WHERE `IDwidget` = '{$widgetID}' AND `published` = '-1';"))
				$this->query("UPDATE `widgets` SET `published` = '{$version}' WHERE `IDwidget` = '{$widgetID}';");
			return true;
		}
		return false;
	}
	
	// Returns an array with all the existent versions of a widget given the widget ID, ordered from the last to the first version.
	function get_all_widget_versions($widgetID) {
		if (!$this->CanIModifyWidget($widgetID))
			return $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `visible` = '1' ORDER BY `version` DESC;");
		else
			return $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' ORDER BY `version` DESC;");
		
	}
	
	// Returns one of the existent versions of a widget given the widget ID and the version number.
	function get_widget_version($widgetID, $version) {
		$widgetID = mysql_escape_mimic($widgetID);
		$version = mysql_escape_mimic($version);
		$result = $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Returns the last version of a widget given the widget ID.
	// if $public = true then the version must be public, otherwise the version is the latest even if it is not public.
	function get_widget_last_version($widgetID, $public = true) {
		$widgetID = mysql_escape_mimic($widgetID);
		$public = $public ? " AND `public` = '1' " : '';
		$result = $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' {$public} ORDER BY `version` DESC LIMIT 1;");
		return count($result) > 0 ? $result[0] : false;
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS - FILES
	#
	# ---------------------------------------------------------------------------
	
	// Get a file of a widget version given its name. The user needs to be the owner or be using the widget.
	function get_widget_version_file($widgetID, $version, $name) {
		if ($version === self::GLOBAL_VERSION || can_be_widget_version($version) && ($this->check_using_widget_user($widgetID) || $this->CanIModifyWidget($widgetID))) {
			$name = mysql_escape_mimic($name);
			return $this->query("SELECT * FROM `files` RIGHT JOIN `widgets-content` USING (`hash`) WHERE `widgets-content`.`IDwidget` = '{$widgetID}' AND `widgets-content`.`version` = '{$version}' AND `widgets-content`.`name` = '{$name}'");
		}
		return false;
	}
	
	// Delete all unlinked files on the files table
	function delete_unlinked_files() {
		$this->query("DELETE FROM `files` WHERE `hash` NOT IN (SELECT `hash` FROM `widgets-content`);");
	}
	
	// Rename a file from a widget version.
	function rename_widget_version_file($widgetID, $version, $hash, $name) {
		if ($this->CanIModifyWidget($widgetID) && can_be_widget_version($version)) {
			$hash = mysql_escape_mimic($hash);
			$name = mysql_escape_mimic($name);
			return $this->query("UPDATE `widgets-content` SET `name` = '{$name}' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `hash` = '{$hash}';");
		}
		return false;
	}
	
	// Get the content files of a widget version.
	// Version can be a number or an array of numbers.
	function get_widget_version_contents($widgetID, $version) {
		$widgetID = mysql_escape_mimic($widgetID);
		if (can_be_widget_version($version)) {
			if (is_array($version)) {
				$sql_versions = "version = '" . implode("' OR version = '", $version) . "'";
				return $this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND ({$sql_versions});");
			}
			return $this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND version = '{$version}';");
		}
	}
	
	// Save a file for a widget version with a name and a mimetype. Checks if the file can be uploaded to the version.
	function upload_widget_version_file($widgetID, $version, $name, $mimetype, &$content) {
		if ($this->CanIModifyWidget($widgetID) && $version === self::GLOBAL_VERSION || can_be_widget_version($version)) {
			
			// Check if the widget version reached the number of files
			if (count($this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';")) >= WIDGET_VERSION_MAX_FILES_NUMBER) {
				return false;
			}
			
			$name = mysql_escape_mimic($name);
			$content = mysql_escape_mimic($content);
			$hash = file_hash($content);
			$this->query("INSERT INTO `widgets-content` (`IDwidget`, `version`, `name`, `hash`, `mimetype`) VALUES ('{$widgetID}', '{$version}', '{$name}', '{$hash}', '{$mimetype}') ON DUPLICATE KEY UPDATE `hash` = VALUES(`hash`), `mimetype` = VALUES (`mimetype`);");
			if (!$this->query("SELECT `hash` FROM `files` WHERE `hash` = '{$hash}';")) {
				return $this->query("INSERT INTO `files` (`hash`, `data`) VALUES ('{$hash}', '{$content}');");
			}
			return true;
		}
		return false;
	}
	
	// Delete a file from a widget version.
	function delete_widget_version_file($widgetID, $version, $hash) {
		if ($this->CanIModifyWidget($widgetID) && can_be_widget_version($version)) {
			$hash = mysql_escape_mimic($hash);
			return $this->query("DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `hash` = '{$hash}';");
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
		return "SELECT `widgets`.*, `widgets-user`.`IDuser`, `widgets-user`.`autoupdate`, `widgets-user`.`version` FROM `widgets` {$JOIN_TYPE} JOIN `widgets-user` ON (`widgets`.`IDwidget` = `widgets-user`.`IDwidget` AND `widgets-user`.`IDuser` = {$this->userID}) ";
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
		return $this->query($this->SELECT_FROM_WIDGETS_JOIN_WIDGETSUER('LEFT') . "WHERE `widgets`.`IDwidget` != ".self::GLOBAL_WIDGET." AND (`ownerID` = '".GLOBAL_USER_ID."' OR `ownerID` = '{$this->userID}' OR `published` > -1);"); // Por poner filtrado de widgets privados
	}
	
	// Returns a list with the widgets available to the user.
	function search_availabe_widgets_user($word) {
		return $this->query($this->SELECT_FROM_WIDGETS_JOIN_WIDGETSUER('LEFT') . "WHERE `widgets`.`IDwidget` != ".self::GLOBAL_WIDGET." AND (`ownerID` = '".GLOBAL_USER_ID."' OR `ownerID` = '{$this->userID}' OR `published` > -1) AND (`name` LIKE '%{$word}%' OR `description` LIKE '%{$word}%' OR `fulldescription` LIKE '%{$word}%');"); // Por poner filtrado de widgets privados
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
	
	// Returns the widget version (number) used given the ID of the widget or the widget object returned by the function get_widgets_user()
	function get_using_widget_version_user(&$WidgetID_o_widgetObject) {
		if (!is_array($WidgetID_o_widgetObject))
			$widgetObject = $this->get_widget_user($WidgetID_o_widgetObject);
		else
			$widgetObject = $WidgetID_o_widgetObject;
		
		if ($widgetObject['autoupdate'] === '1') {
			$widgetID = $widgetObject['IDwidget'];
			
			$user_owned_published = $this->query("SELECT `published` FROM `widgets` WHERE `IDwidget` = '{$widgetID}' AND `ownerID` = '{$this->userID}'");
			
			if (isset($user_owned_published[0]['published'])) {
				if ($user_owned_published[0]['published'] === '-1')
					return false;
				else
					$widgetObject = $this->get_widget_last_version($widgetID, false);
			} else
				$widgetObject = $this->get_widget_last_version($widgetID, true);
		}
		return $widgetObject['version'];
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# USERS - DROPBOX
	#
	# ---------------------------------------------------------------------------
	
	// Returns all the tokens that the current user have
	function getAllAccessToken($ID) {
		if(!$ID) $ID = $this->userID;
		$resp = $this->query("SELECT `dropbox_accessToken` FROM `access-token` WHERE `IDuser` = '{$ID}';");
		return isset($resp[0]) ? $resp[0] : array();
	}
	
	// Set the dropbox token of the current user
	function setDropboxAccessToken($accessToken) {
		return $this->query("INSERT INTO `access-token` (`IDuser`, `dropbox_accessToken`) VALUES ('{$this->userID}', '{$accessToken}') ON DUPLICATE KEY UPDATE `dropbox_accessToken` = '{$accessToken}';");
	}
	
	// Delete the dropbox token of the current user
	function delDropboxAccessToken($accessToken) {
		return $this->query("UPDATE `access-token` SET `dropbox_accessToken` = '' WHERE `IDuser` = '{$this->userID}';");
	}
	
	
	
	// --------------------------------------------------------
	
	// (SET) Cache a result. $query is the sql to be cached, $result is the array of the response.
	function cacheResult($query, $result) {
		$this->cache[$query] = $result;
	}
	
	// (GET) Cache a result. $query is the sql to search in the cache, $result is the array of the response.
	function queryCache($query) {
		return isset($this->cache[$query]) ? $this->cache[$query] : false;
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
	return custom_hmac('md5', $password, $salt);
}

function can_be_widget_version($version) {
	return isInteger($version) && $version >= 0;
}

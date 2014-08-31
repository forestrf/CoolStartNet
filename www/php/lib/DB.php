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
	
	function Open($host=null, $user=null, $pass=null, $bd=null){
		if($host !== null)
			$this->host = $host;
		if($user !== null)
			$this->user = $user;
		if($pass !== null)
			$this->pass = $pass;
		if($bd !== null)
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
	private function query($query, $cacheable = false){
		if($cacheable){
			$cached = $this->queryCache($query);
			if($cached !== false){
				return $cached;
			}
		}
		
		if($this->opened_connection === false){
			if(!$this->Open()){
				return false;
			}
			$this->opened_connection = true;
		}
		
		$result = $this->mysqli->query($query, MYSQLI_USE_RESULT);
		if(strpos($query, 'INSERT') !== false){
			$this->LAST_MYSQL_ID = $this->mysqli->insert_id;
		}
		if($result === false || $result === true){
			return $result;
		}
		
		$resultArray = array();
		while($rt = $result->fetch_array(MYSQLI_ASSOC)){$resultArray[] = $rt;};
		if($cacheable){
			$this->cacheResult($query, $resultArray);
		}
		return $resultArray;
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# USERS
	#
	# ---------------------------------------------------------------------------
	
	// Check if exists a nick.
	function nick_exists($nick){
		$nick = mysql_escape_mimic($nick);
		return count($this->query("SELECT * FROM `users` WHERE `nick` = '{$nick}'", true)) > 0;
	}
	
	// Insert a new user. Data previously validated and sanitized
	function create_new_user($nick, $password, $email){
		require_once __DIR__.'/../functions/generic.php';
		$nick = mysql_escape_mimic($nick);
		$password = hash_password($password);
		$email = mysql_escape_mimic($email);
		$rnd = mysql_escape_mimic(utf8_encode(random_string(32)));
		return $this->query("INSERT INTO `users` (`nick`, `password`, `email`, `RND`) VALUES ('{$nick}', '{$password}', '{$email}', '{$rnd}');") === true;
	}
	
	// Returns the user after validating the nick and the password or returns false if the user doesn't match.
	function check_nick_password($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = hash_password($password);
		$result = $this->query("SELECT * FROM `users` WHERE `nick` = '{$nick}' AND `password` = '{$password}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS
	#
	# ---------------------------------------------------------------------------
	
	// Returns the widget configuration given a widget name or false if the widget doesn't exists.
	function get_widget($name){
		$name = mysql_escape_mimic($name);
		$result = $this->query("SELECT * FROM `widgets` WHERE `name` = '{$name}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Returns the widget configuration given a widget ID or false if the widget doesn't exists.
	function get_widget_by_ID($ID){
		$ID = mysql_escape_mimic($ID);
		$result = $this->query("SELECT * FROM `widgets` WHERE `ID` = '{$ID}';");
		return count($result) > 0 ? $result[0] : false;
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
	function get_variable(&$widgetID_variable){
		// Make all the operations in one sql call.
		$SQL_statement = array();
		foreach($widgetID_variable as $widgetID => &$variables){
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			$widgetID_calc = $widgetID === 'global' ? '-1' : $widgetID; //global is a invisible widget with id -1
			
			// Ignore value
			foreach($variables as $variable => &$value){
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "(`IDwidget` = '{$widgetID_calc}' AND `variable` = '{$variable}')";
			}
		}
		
		return $this->query("SELECT `IDwidget`, `variable`, `value` FROM `variables` WHERE `IDuser` = '{$_SESSION['user']['ID']}' AND (".implode('OR', $SQL_statement).");");
	}
	
	
	function check_variable(&$widgetID_variable){
		// Make all the operations in one sql call.
		$SQL_statement = array();
		foreach($widgetID_variable as $widgetID => &$variables){
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			$widgetID_calc = $widgetID === 'global' ? '-1' : $widgetID; //global is a invisible widget with id -1
			
			// Ignore value
			foreach($variables as $variable => &$value){
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "(`IDwidget` = '{$widgetID_calc}' AND `variable` = '{$variable}')";
			}
		}
		
		return $this->query("SELECT `IDwidget`, `variable` FROM `variables` WHERE `IDuser` = '{$_SESSION['user']['ID']}' AND (".implode('OR', $SQL_statement).");");
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
	function set_variable(&$widgetID_variable_value){
		$occupied = $this->get_user_size_variable();
		
		// Make all the operations in one sql call.
		$SQL_statement = array();
		foreach($widgetID_variable_value as $widgetID => &$variable_value){
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			$widgetID_calc = $widgetID === 'global' ? '-1' : $widgetID; //global is a invisible widget with id -1
			
			foreach($variable_value as $variable => &$value){
				$value = json_encode($value);
				$occupied[$widgetID_calc][$variable] = strlen($value);
				$value = mysql_escape_mimic($value);
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "('{$_SESSION['user']['ID']}', '{$widgetID_calc}', '{$variable}', '{$value}')";
			}
		}

		if($this->calculate_occupied_size_from_object($occupied) < USER_MAX_BYTES_STORED_DB){
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
	function get_user_size_variable(){
		$pre_occupied = $this->query("SELECT `IDwidget`, `variable`, LENGTH(`value`) AS `total_size` FROM `variables` WHERE `IDuser` = '{$_SESSION['user']['ID']}';");
		$occupied = array();
		for($i = 0; $i < count($pre_occupied); $i++){
			$occupied[$pre_occupied[$i]['IDwidget']][$pre_occupied[$i]['variable']] = intval($pre_occupied[$i]['total_size']);
		}
		
		return $occupied;
	}
	
	function calculate_occupied_size_from_object(&$object){
		$sum = 0;
		foreach($object as $IDwidget => &$variable){
			foreach($variable as &$size){
				$sum += $size;
			}
		}
		
		return $sum;
	}
	
	function del_variable(&$widgetID_variable_value){
		// Make all the operations in one sql call.
		$SQL_statement = array();
		foreach($widgetID_variable_value as $widgetID => &$variable_value){
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			$widgetID_calc = $widgetID === 'global' ? '-1' : $widgetID; //global is a invisible widget with id -1
			
			foreach($variable_value as $variable => &$value){
				$variable = mysql_escape_mimic($variable);
				$SQL_statement[] = "(`IDwidget` = '{$widgetID_calc}' AND `variable` = '{$variable}')";
			}
		}
		
		return $this->query("DELETE FROM `variables` WHERE `IDuser` = '{$_SESSION['user']['ID']}' AND (".implode(' OR ', $SQL_statement).");");
	}
	
	function delall_variable(&$widgetID_variable_value, $private_only = true){
		// Make all the operations in one sql call.
		$SQL_statement = array();
		foreach($widgetID_variable_value as $widgetID => &$variable_value){
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			if($private_only && $widgetID === 'global'){
				continue;
			}
			$widgetID_calc = $widgetID === 'global' ? '-1' : $widgetID; //global is a invisible widget with id -1
			
			$SQL_statement[] = "`IDwidget` = '{$widgetID_calc}'";
		}
		
		return $this->query("DELETE FROM `variables` WHERE `IDuser` = '{$_SESSION['user']['ID']}' AND (".implode(' OR ', $SQL_statement).");");
	}
	
	// Create a widget.
	function create_widget($name){
		$name = mysql_escape_mimic($name);
		$result = $this->query("SELECT `ID` FROM `widgets` WHERE `name` = '{$name}';");
		if(!$result){
			return $this->query("INSERT INTO `widgets` (`name`, `ownerID`) VALUES ('{$name}', '{$_SESSION['user']['ID']}');");
		}
		return false;
	}
	
	// Delete a widget given the ID of the widget.
	// Deleting a widget also deletes the widget variables of users and users lose it if they are using it.
	// Doesn't delete from the table 'files' because other file can has the same hash. The unlinked content is deleted from other function that is called from a cronjob.
	function delete_widget($widgetID){
		if($this->CanIModifyWidget($widgetID) && $this->query("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `published` = '-1';")){
			$this->query("DELETE FROM `widgets` WHERE `ID` = '{$widgetID}';");
			$this->query("DELETE FROM `variables` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}';");
			return true;
		}
		return false;
	}
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS - VERSIONS
	#
	# ---------------------------------------------------------------------------
	
	// Set a public version as visible or invisible. $visible = true o false.
	function set_widget_version_visibility($widgetID, $version, $visible){
		if($this->CanIModifyWidget($widgetID) && can_be_widget_version($version)){
			$visible = $visible ? 1 : 0;
			return $this->query("UPDATE `widgets-versions` SET `visible` = '{$visible}' WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `version` = '{$version}';");
		}
		return false;
	}
	
	// Set all public widget versions as invisible.
	function hide_all_widget_versions($widgetID){
		if($this->CanIModifyWidget($widgetID)){
			return $this->query("UPDATE `widgets-versions` SET `visible` = '0' WHERE `IDwidget` = '{$widgetID}';");
		}
		return false;
	}
	
	// Set a comment for the widget version.
	function set_widget_comment($widgetID, $version, $comment){
		if($this->CanIModifyWidget($widgetID) && can_be_widget_version($version)){
			$comment = mysql_escape_mimic($comment);
			return $this->query("UPDATE `widgets-versions` SET `comment` = '{$comment}' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
		}
		return false;
	}
	
	// Delete a private widget version.
	function delete_private_widget_version($widgetID, $version){
		if(can_be_widget_version($version) && $this->CanIModifyWidget($widgetID) && $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `public` = '0';")){
			$this->query("DELETE FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			$this->query("DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			return true;
		}
		return false;
	}
	
	// Set a public widget version as the default public widget version.
	function set_widget_default_version($widgetID, $version){
		if(
			$this->CanIModifyWidget($widgetID) && can_be_widget_version($version) &&
			// Check if the version exists and is public
			$this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `version` = '{$version}';")
		){
			return $this->query("UPDATE `widgets` SET `published` = '{$version}' WHERE `ID` = '{$widgetID}';");
		}
		return false;
	}
	
	// Create a version of the widget.
	function create_widget_version($widgetID){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		$new_version = $this->get_widget_last_version($widgetID, false);
		$new_version = $new_version['version'];
		if(!$new_version){
			$new_version = 0;
		}
		++$new_version;
		return $this->query("INSERT INTO `widgets-versions` (`IDwidget`, `version`) VALUES ('{$widgetID}', '{$new_version}');");
	}
	
	// Check if a version can be publicated.
	function can_publicate_widget_version_check($widgetID, $version){
		if($this->CanIModifyWidget($widgetID) && can_be_widget_version($version)){
			return $this->query("SELECT `name` FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `name` = 'main.js';") ? true : false;
		}
		return false;
	}
	
	// Publicate a widget version (cannot be undone).
	function publicate_widget_version($widgetID, $version){
		if($this->CanIModifyWidget($widgetID) && can_be_widget_version($version) &&
		$this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';")){
			$this->query("UPDATE `widgets-versions` SET `public` = '1' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			if($this->query("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `published` = '-1';")){
				$this->query("UPDATE `widgets` SET `published` = '{$version}' WHERE `ID` = '{$widgetID}';");
			}
			return true;
		}
		return false;
	}
	
	// Returns an array with all the existent versions of a widget given the widget ID, ordered from the last to the first version.
	function get_all_widget_versions($widgetID){
		if(!$this->CanIModifyWidget($widgetID)){
			return $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `visible` = '1' ORDER BY `version` DESC;");
		}
		else{
			return $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' ORDER BY `version` DESC;");
		}
	}
	
	// Returns one of the existent versions of a widget given the widget ID and the version number.
	function get_widget_version($widgetID, $version){
		$widgetID = mysql_escape_mimic($widgetID);
		$version = mysql_escape_mimic($version);
		$result = $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Returns the last version of a widget given the widget ID.
	// if $public = true then the version must be public, otherwise the version is the latest even if it is not public.
	function get_widget_last_version($widgetID, $public = true){
		$widgetID = mysql_escape_mimic($widgetID);
		$public = $public ? " AND `public` = '1' " : '';
		$result = $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' {$public} ORDER BY `version` DESC LIMIT 1;");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// POR TESTEAR
	// Retorna la versión default o de lo contrario la versión pública más avanzada
	function get_widget_default_version($widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		$public_version = $this->query("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `published` > -1;");
		if($public_version){
			$public_version = $public_version[0]['published'];
			$public_version_1 = $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `visible` = '1' AND `version` = '{$public_version}' ORDER BY `version`;");
			if($public_version_1){
				return $public_version_1[0];
			}
			else{
				return $this->get_widget_last_version($widgetID, true);
			}
		}
		else{
			if($this->query("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `ownerID` = '{$_SESSION['user']['ID']}';")){
				return $this->get_widget_last_version($widgetID, false);
			}	
		}
		return false;
	}
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS - FILES
	#
	# ---------------------------------------------------------------------------
	
	// Get a file of a widget version given its name. The user needs to be the owner or be using the widget.
	function get_widget_version_file($widgetID, $version, $name){
		if(can_be_widget_version($version) && ($this->check_using_widget_user($widgetID) || $this->CanIModifyWidget($widgetID))){
			$name = mysql_escape_mimic($name);
			return $this->query("SELECT * FROM `files` WHERE `hash` = (SELECT `hash` FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `name` = '{$name}');");
		}
		return false;
	}
	
	// Delete all unlinked files on the files table
	function delete_unlinked_files(){
		$this->query("DELETE FROM `files` WHERE `hash` NOT IN (SELECT `hash` FROM `widgets-content`);");
	}
	
	// Rename a file from a widget version.
	function rename_widget_version_file($widgetID, $version, $hash, $name){
		if($this->CanIModifyWidget($widgetID) && can_be_widget_version($version)){
			$hash = mysql_escape_mimic($hash);
			$name = mysql_escape_mimic($name);
			return $this->query("UPDATE `widgets-content` SET `name` = '{$name}' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `hash` = '{$hash}';");
		}
		return false;
	}
	
	// Get the content files of a widget version.
	// Version can be a number or an array of numbers.
	function get_widget_version_contents($widgetID, $version){
		$widgetID = mysql_escape_mimic($widgetID);
		if(can_be_widget_version($version)){
			if(is_array($version)){
				$sql_versions = "version = '" . implode("' OR version = '", $version) . "'";
				return $this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND ({$sql_versions});");
			}
			else{
				return $this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND version = '{$version}';");
			}
		}
	}
	
	// Save a file for a widget version with a name and a mimetype. Checks if the file can be uploaded to the version.
	function upload_widget_version_file($widgetID, $version, $name, $mimetype, &$content){
		if($this->CanIModifyWidget($widgetID) && can_be_widget_version($version)){
			
			// Check if the widget version reached the number of files
			if(count($this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';")) >= WIDGET_VERSION_MAX_FILES_NUMBER){
				return false;
			}
			
			$name = mysql_escape_mimic($name);
			$content = mysql_escape_mimic($content);
			$hash = file_hash($content);
			$this->query("INSERT INTO `widgets-content` (`IDwidget`, `version`, `name`, `hash`) VALUES ('{$widgetID}', '{$version}', '{$name}', '{$hash}');");
			if(!$this->query("SELECT * FROM `files` WHERE `hash` = '{$hash}';")){
				return $this->query("INSERT INTO `files` (`hash`, `data`, `mimetype`) VALUES ('{$hash}', '{$content}', '{$mimetype}');");
			}
			return true;
		}
		return false;
	}
	
	// Delete a file from a widget version.
	function delete_widget_version_file($widgetID, $version, $hash){
		if($this->CanIModifyWidget($widgetID) && can_be_widget_version($version)){
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
	
	// Returns a list with the widgets of the user.
	function get_widgets_user(){
		return $this->query("SELECT `widgets`.*, `widgets-user`.`autoupdate`, `widgets-user`.`version` FROM `widgets-user` LEFT JOIN `widgets` ON `widgets-user`.`IDwidget` = `widgets`.`ID` WHERE `IDuser` = '{$_SESSION['user']['ID']}';");
	}
	
	// Returns a widget of the user.
	function get_widget_user($widgetID){
		$result = $this->query("SELECT `widgets`.*, `widgets-user`.`autoupdate`, `widgets-user`.`version` FROM `widgets-user` LEFT JOIN `widgets` ON `widgets-user`.`IDwidget` = `widgets`.`ID` WHERE `IDuser` = '{$_SESSION['user']['ID']}' AND `widgets`.`ID` = '{$widgetID}';");
		return $result ? $result[0] : false;
	}
	
	// Returns a list with the widgets available to the user.
	function get_availabe_widgets_user(){
		return $this->query("SELECT * FROM `widgets` WHERE `ownerID` = '-1' OR `ownerID` = '{$_SESSION['user']['ID']}' OR `published` > -1;"); // Por poner filtrado de widgets privados
	}
	
	// Returns a list with the widgets owned by the user.
	function get_widgets_user_owns(){
		return $this->query("SELECT * FROM `widgets` WHERE `ownerID` = '{$_SESSION['user']['ID']}';");
	}
	
	// Returns true if the widget can be manipulated by the user (true if user is the owner of te widget). Otherwise returns false.
	function CanIModifyWidget(&$widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		return $this->query("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `ownerID` = '{$_SESSION['user']['ID']}';") ? true : false;
	}
	
	// Makes the user stop using a widget without deleting the widget configurations.
	function remove_using_widget_user($widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		$this->query("DELETE FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}' AND `IDuser` = '{$_SESSION['user']['ID']}';");
	}
	
	// Makes the user to use a widget.
	function add_using_widget_user($widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		
		// Checks if the users already uses the widget.
		if(count($this->query("SELECT * FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}' AND `IDuser` = '{$_SESSION['user']['ID']}';")) === 0){
			$this->query("INSERT INTO `widgets-user` (`IDwidget`, `IDuser`) VALUES ('{$widgetID}', '{$_SESSION['user']['ID']}');");
		}
	}
	
	// Returns true if the user is using the widget, otherwise returns false.
	function check_using_widget_user(&$widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		return $this->query("SELECT * FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}' AND `IDuser` = '{$_SESSION['user']['ID']}';") ? true : false;
	}
	
	// Returns the widget version (number) used given the ID of the widget or the widget object returned by the function get_widgets_user()
	function get_using_widget_version_user(&$WidgetID_o_widgetObject){
		if(!is_array($WidgetID_o_widgetObject)){
			// WidgetID = $WidgetID_o_widgetObject
			$widgetObject = $this->get_widget_user($WidgetID_o_widgetObject);
		}
		else{
			$widgetObject = &$WidgetID_o_widgetObject;
		}
		
		if($widgetObject['autoupdate'] === '1'){
			$version = $this->get_widget_default_version($widgetObject['ID']);
			return $version['version'];
		}
		else{
			return $widgetObject['version'];
		}
	}
	
	// Sets the widget version (number) used for the especified widget ID for the user.
	function set_using_widget_version_user($widgetID, $version){
		if(can_be_widget_version($version)){
			$widgetID = mysql_escape_mimic($widgetID);
		
			// Check if the user has the rights to have any version of the widget, if the version exists and the privileges of the version.
			if(!$this->CanIModifyWidget($widgetID)){
				if(!$this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `public` = '1' AND `visible` = '1';")){
					return false;
				}
			}
			else{
				if(!$this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';")){
					return false;
				}
			}
			
			return $this->query("UPDATE `widgets-user` SET `autoupdate` = '0', `version` = '{$version}' WHERE `IDuser` = '{$_SESSION['user']['ID']}' AND `IDwidget` = '{$widgetID}';");
		}
		return false;
	}
	
	// Sets the widget version used to autoupdate for the especified widget ID for the user.
	function set_using_widget_version_autoupdate_user($widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		return $this->query("UPDATE `widgets-user` SET `autoupdate` = '1', `version` = '0' WHERE `IDuser` = '{$_SESSION['user']['ID']}' AND `IDwidget` = '{$widgetID}';");
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# USERS - DROPBOX
	#
	# ---------------------------------------------------------------------------
	
	// Returns all the tokens that the current user have
	function getAllAccessToken(){
		$resp = $this->query("SELECT `dropbox_accessToken` FROM `access-token` WHERE `IDuser` = '{$_SESSION['user']['ID']}';");
		return isset($resp[0]) ? $resp[0] : array();
	}
	
	// Set the dropbox token of the current user
	function setDropboxAccessToken($accessToken){
		return $this->query("INSERT INTO `access-token` (`IDuser`, `dropbox_accessToken`) VALUES ('{$_SESSION['user']['ID']}', '{$accessToken}') ON DUPLICATE KEY UPDATE `dropbox_accessToken` = '{$accessToken}';");
	}
	
	// Delete the dropbox token of the current user
	function delDropboxAccessToken($accessToken){
		return $this->query("UPDATE `access-token` SET `dropbox_accessToken` = '' WHERE `IDuser` = '{$_SESSION['user']['ID']}';");
	}
	
	
	
	// --------------------------------------------------------
	
	// (SET) Cache a result. $query is the sql to be cached, $result is the array of the response.
	function cacheResult($query, $result){
		$this->cache[$query] = $result;
	}
	
	// (GET) Cache a result. $query is the sql to search in the cache, $result is the array of the response.
	function queryCache($query){
		return isset($this->cache[$query]) ? $this->cache[$query] : false;
	}
}

// Copy of mysql_real_escape_string to use it without an opened connection.
// http://es1.php.net/mysql_real_escape_string
function mysql_escape_mimic($inp){
	if(is_array($inp))
		return array_map(__METHOD__, $inp);

	if(!empty($inp) && is_string($inp)){
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
	}

	return $inp;
}

function hash_password($password){
	return custom_hmac('md5', $password, USER_PASSWORD_HMAC_SEED);
}

function can_be_widget_version($version){
	return isInteger($version) && $version >= 0;
}

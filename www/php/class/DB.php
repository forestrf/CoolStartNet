<?php

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../functions/generic.php';

// All the queries to the database are here. Change the database engine or the queries only have to be done here.
class DB {
	
	// Login data to access the database. change in the config file.
	private $host = MYSQL_HOST;
	private $user = MYSQL_USER;
	private $pass = MYSQL_PASSWORD;
	private $bd   = MYSQL_DATABASE;
	
	private $mysqli;
	
	private $opened_connection = false;
	
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
		$this->mysqli->set_charset("utf8");
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
		
		try{
			$result = $this->mysqli->query($query, MYSQLI_USE_RESULT);
			if(strpos($query, 'INSERT')!==false){
				$this->LAST_MYSQL_ID = $this->mysqli->insert_id;
			}
			if($result === false){
				throw new Exception('Error: '.$this->mysqli->error);
				return false;
			}
			elseif($result === true){
				return true;
			}
		}
		catch(Exception $e){
			return false;
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
	function existeNick($nick){
		$nick = mysql_escape_mimic($nick);
		return count($this->query("SELECT * FROM `users` WHERE `nick` = '{$nick}'", true)) > 0;
	}
	
	// Insert a new user.
	function insertaUsuario($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = hash_password(mysql_escape_mimic($password));
		$rnd = random_string(32);
		return $this->query("INSERT INTO `users` (`nick`, `password`, `RND`) VALUES ('{$nick}', '{$password}', '{$rnd}')") === true;
	}
	
	// Returns the user after validating the nick and the password or returns false if the user doesn't match.
	function NickPasswordValidacion($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = hash_password(mysql_escape_mimic($password));
		$result = $this->query("SELECT * FROM `users` WHERE `nick` = '{$nick}' AND `password` = '{$password}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS
	#
	# ---------------------------------------------------------------------------
	
	// Returns the widget configuration given a widget name or false if the widget doesn't exists.
	function getWidget($nombre){
		$nombre = mysql_escape_mimic($nombre);
		$result = $this->query("SELECT * FROM `widgets` WHERE `name` = '{$nombre}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Returns the widget configuration given a widget ID or false if the widget doesn't exists.
	function getWidgetPorID($ID){
		$ID = mysql_escape_mimic($ID);
		$result = $this->query("SELECT * FROM `widgets` WHERE `ID` = '{$ID}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Returns a variable of the user.
	// Change $ID to get a variable from a specific user.
	// $widgetID_variable must be an array that follows the next pattern:
	/*
		array(
			'widgetID' => array(
				'variable' => '', ...
			), ...
		);
	*/
	function getVariable($widgetID_variable, $ID = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		
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
		
		return $this->query("SELECT `IDwidget`, `variable`, `value` FROM `variables` WHERE `IDuser` = '{$ID}' AND ".implode('OR', $SQL_statement).";");
	}
	
	// Doesn't check if the widget exists. This check is done in api.php
	// POR HACER: Limitar tamaño de lo que se puede guardar.
	// Change $ID to set a variable to a specific user.
	// $widgetID_variable_value must be an array that follows the next pattern:
	/*
		array(
			'widgetID' => array(
				'variable' => 'value', ...
			), ...
		);
	*/
	function setVariable($widgetID_variable_value, $ID = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		
		// Make all the operations in one sql call.
		$SQL_statement = array();
		foreach($widgetID_variable_value as $widgetID => &$variable_value){
			$widgetID = mysql_escape_mimic($widgetID);
			
			// Global widget handler here
			$widgetID_calc = $widgetID === 'global' ? '-1' : $widgetID; //global is a invisible widget with id -1
			
			foreach($variable_value as $variable => &$value){
				$variable = mysql_escape_mimic($variable);
				$value = mysql_escape_mimic($value);
				$SQL_statement[] = "('{$ID}', '{$widgetID_calc}', '{$variable}', '{$value}')";
			}
		}
		
		return $this->query("INSERT INTO `variables` (`IDuser`, `IDwidget`, `variable`, `value`) VALUES ".implode(',', $SQL_statement)." ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);");
	}
	
	// Create a widget.
	function creaWidget($nombre, $ID = null){
		$nombre = mysql_escape_mimic($nombre);
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		$result = $this->query("SELECT `ID` FROM `widgets` WHERE `name` = '{$nombre}';");
		if(!$result){
			return $this->query("INSERT INTO `widgets` (`name`, `ownerID`) VALUES ('{$nombre}', '{$ID}');");
		}
		return false;
	}
	
	// Delete a widget given the ID of the widget.
	// Only if $admin = true a public widget can be deleted.
	// Deleting a widget also deletes the widget variables of users and users lose it if they are using it.
	// Doesn't delete from the table 'files' because other file can has the same hash. The unlinked content is deleted from other function that is called from a cronjob.
	function borraWidget($widgetID, $admin = false){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		if($admin || $this->query("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `published` = '-1';")){
			$this->query("DELETE FROM `widgets` WHERE `ID` = '{$widgetID}';");
			$this->query("DELETE FROM `variables` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}';");
			$this->query("DELETE FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}';");
		}
	}
	
	// Returns an array with all the existent versions of a widget given the widget ID, ordered from the last to the first version.
	function getWidgetVersiones($widgetID){
		if(!$this->CanIModifyWidget($widgetID)){
			return $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `visible` = '1' ORDER BY `version` DESC;");
		}
		else{
			return $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' ORDER BY `version` DESC;");
		}
	}
	
	// Returns one of the existent versions of a widget given the widget ID and the version number.
	function getWidgetVersion($widgetID, $version){
		$widgetID = mysql_escape_mimic($widgetID);
		$version = mysql_escape_mimic($version);
		$result = $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Returns the last version of a widget given the widget ID.
	// if $public = true then the version must be public, otherwise the version is the latest even if it is not public.
	function getWidgetLastVersion($widgetID, $public = true){
		$widgetID = mysql_escape_mimic($widgetID);
		$public = $publico?" AND `public` = '1' ":'';
		$result = $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' {$public} ORDER BY `version` DESC LIMIT 1;");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// POR TESTEAR
	// Retorna la versión default o de lo contrario la versión pública más avanzada
	function getWidgetDefaultVersion($widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		$version_publica = $this->query("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `published` > -1;");
		if($version_publica){
			$version_publica = $version_publica[0]['published'];
			$version_publica_concreta = $this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `visible` = '1' AND `version` = '{$version_publica}' ORDER BY `version`;");
			if($version_publica_concreta){
				return $version_publica_concreta[0];
			}
			else{
				return $this->getWidgetLastVersion($widgetID, true);
			}
		}
		else{
			$ID = &$_SESSION['user']['ID'];
			if($this->query("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `ownerID` = '{$ID}';")){
				return $this->getWidgetLastVersion($widgetID, false);
			}	
		}
		return false;
	}
	
	// Crear una versión del widget
	function creaWidgetVersion($widgetID){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		$new_version = $this->getWidgetLastVersion($widgetID, false)['version'];
		if(!$new_version){
			$new_version = 0;
		}
		++$new_version;
		return $this->query("INSERT INTO `widgets-versions` (`IDwidget`, `version`) VALUES ('{$widgetID}', '{$new_version}');");
	}
	
	// Publicar una versión del widget (no se puede deshacer)
	function canPublicWidgetVersion($widgetID, $version){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		return $this->query("SELECT `name` FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `name` = 'main.js';") ? true : false;
	}
	
	// Publicar una versión del widget (no se puede deshacer)
	function publicaWidgetVersion($widgetID, $version){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		if($this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';")){
			$this->query("UPDATE `widgets-versions` SET `public` = '1' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			if($this->query("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `published` = '-1';")){
				$this->query("UPDATE `widgets` SET `published` = '{$version}' WHERE `ID` = '{$widgetID}';");
			}
			return true;
		}
		return false;
	}
	
	// Editar comentario de una versión del widget
	function editarWidgetComentario($widgetID, $version, $comentario){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		$comentario = mysql_escape_mimic($comentario);
		return $this->query("UPDATE `widgets-versions` SET `comment` = '{$comentario}' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
	}
	
	// Borrar una versión no publicada del widget
	function borraWidgetVersion($widgetID, $version){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		if($this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `public` = '0';")){
			$this->query("DELETE FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			$this->query("DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			return true;
		}
		return false;
	}
	
	// Hacer de una versión pública la default
	function versionWidgetDefault($widgetID, $version){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		// Comprobar si existe la versión a hacer default
		if($this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `version` = '{$version}';")){
			return $this->query("UPDATE `widgets` SET `published` = '{$version}' WHERE `ID` = '{$widgetID}';");
		}
		return false;
	}
	
	// Marcar versión pública como visible o invisible. $visible = true o false
	function versionWidgetVisibilidad($widgetID, $version, $visible){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		$visible = $visible?1:0;
		return $this->query("UPDATE `widgets-versions` SET `visible` = '{$visible}' WHERE `IDwidget` = '{$widgetID}' AND `public` = '1' AND `version` = '{$version}';");
	}
	
	// Marcar todas las versiones como invisibles
	function ocultarTodasVersionesWidget($widgetID){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		$this->query("UPDATE `widgets-versions` SET `visible` = '0' WHERE `IDwidget` = '{$widgetID}';");
	}
	
	// Version puede ser un número o un array de números (aunque no creo que se use)
	function getWidgetContenidoVersion($widgetID, $version){
		$widgetID = mysql_escape_mimic($widgetID);
		if(!isInteger($version) || $version < 0){
			return false;
		}
		if(is_array($version)){
			$versiones_sql = "version = '".implode("' OR version = '", $version)."'";
			return $this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND ({$versiones_sql});");
		}
		else{
			return $this->query("SELECT * FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND version = '{$version}';");
		}
	}
	
	// Guardar archivo para un widget y versión específica. Comprobar antes que se puede subir un archivo para esa versión
	function widgetVersionGuardarArchivo($widgetID, $widgetVersion, $name, $tipo, &$content){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		$widgetVersion = mysql_escape_mimic($widgetVersion);
		$name = mysql_escape_mimic($name);
		$content = mysql_escape_mimic($content);
		$hash = file_hash($content);
		$this->query("INSERT INTO `widgets-content` (`IDwidget`, `version`, `name`, `hash`) VALUES ('{$widgetID}', '{$widgetVersion}', '{$name}', '{$hash}');");
		if(!$this->query("SELECT * FROM `files` WHERE `hash` = '{$hash}';")){
			$this->query("INSERT INTO `files` (`hash`, `data`, `mimetype`) VALUES ('{$hash}', '{$content}', '{$tipo}');");
		}
	}
	
	// Borrar archivo de un widget y versión específica.
	function widgetVersionBorrarArchivo($widgetID, $widgetVersion, $hash){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		$widgetVersion = mysql_escape_mimic($widgetVersion);
		$hash = mysql_escape_mimic($hash);
		return $this->query("DELETE FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$widgetVersion}' AND `hash` = '{$hash}';");
	}
	
	// Borrar archivo de un widget y versión específica.
	function widgetVersionRenombraArchivo($widgetID, $widgetVersion, $hash, $name){
		if(!$this->CanIModifyWidget($widgetID)){
			return false;
		}
		$widgetVersion = mysql_escape_mimic($widgetVersion);
		$hash = mysql_escape_mimic($hash);
		$name = mysql_escape_mimic($name);
		return $this->query("UPDATE `widgets-content` SET `name` = '{$name}' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$widgetVersion}' AND `hash` = '{$hash}';");
	}
	
	// Guardar archivo para un widget y versión específica. Comprobar antes que se puede subir un archivo para esa versión
	function widgetVersionGetArchivo($widgetID, $widgetVersion, $name){
		if($this->CanIModifyWidget($widgetID) || $this->widgetEnListaUsuario($widgetID)){
			$widgetVersion = mysql_escape_mimic($widgetVersion);
			$name = mysql_escape_mimic($name);
			return $this->query("SELECT * FROM `files` WHERE `hash` = (SELECT `hash` FROM `widgets-content` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$widgetVersion}' AND `name` = '{$name}');");
		}
		return false;
	}
	
	// Delete all unlinked files on the files table
	function delete_unlinked_files(){
		$this->query("DELETE FROM `files` WHERE `hash` NOT IN (SELECT `hash` FROM `widgets-content`);");
	}
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS - USERS
	#
	# ---------------------------------------------------------------------------
	
	// Retorna un listado con los widgets que tiene el usuario. Si se especifica ID se buscará los widgets del usuario con esa id, de lo contrario se usa el actual usuario.
	function getWidgetsDelUsuario($ID = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		return $this->query("SELECT `widgets`.*, `widgets-user`.`autoupdate`, `widgets-user`.`version` FROM `widgets-user` LEFT JOIN `widgets` ON `widgets-user`.`IDwidget` = `widgets`.`ID` WHERE `IDuser` = '{$ID}';");
	}
	
	// Retorna un widget que tiene el usuario. Si se especifica ID se buscará los widgets del usuario con esa id, de lo contrario se usa el actual usuario.
	function getWidgetDelUsuario($widgetID, $ID = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		$result = $this->query("SELECT `widgets`.*, `widgets-user`.`autoupdate`, `widgets-user`.`version` FROM `widgets-user` LEFT JOIN `widgets` ON `widgets-user`.`IDwidget` = `widgets`.`ID` WHERE `IDuser` = '{$ID}' AND `widgets`.`ID` = '{$widgetID}';");
		return $result ? $result[0] : false;
	}
	
	// Retorna un listado con los widgets que puede usar el usuario en la página principal.
	function getWidgetsDisponiblesUsuario($ID = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		return $this->query("SELECT * FROM `widgets` WHERE `ownerID` = '-1' OR `ownerID` = '{$ID}' OR `published` > -1;"); // Por poner filtrado de widgets privados
	}
	
	// Retorna un listado con los widgets propiedad del usuario sobre los cuales tiene el control, como borrarlos o editarlos
	// ID puede dejarse null si se llama con $admin = true
	function getWidgetsControlUsuario($ID = null, $admin = false){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		if($admin){
			return $this->query("SELECT * FROM `widgets`;");
		}
		else{
			return $this->query("SELECT * FROM `widgets` WHERE `ownerID` = '{$ID}';");
		}
	}
	
	// Retorna true si se puede manipular el widget por el usuario
	function CanIModifyWidget(&$widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		return $this->query("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `ownerID` = '{$_SESSION['user']['ID']}';")?true:false;
	}
	
	// Quitar un widget de un usuario no borra la configuraciones del widget de ese usuario.
	function quitarWidgetDelUsuario($widgetID, $ID = null){
		$widgetID = mysql_escape_mimic($widgetID);
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		$this->query("DELETE FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}' AND `IDuser` = '{$ID}';");
	}
	
	// Agregar un widget a un usuario.
	function agregarWidgetAlUsuario($widgetID, $ID = null){
		$widgetID = mysql_escape_mimic($widgetID);
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		
		// Comprobar si el usuario ya tenía el widget
		if(count($this->query("SELECT * FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}' AND `IDuser` = '{$ID}';")) === 0){
			$this->query("INSERT INTO `widgets-user` (`IDwidget`, `IDuser`) VALUES ('{$widgetID}', '{$ID}');");
		}
	}
	
	// Retorna true si el usuario usa el widget, false de lo contrario.
	function widgetEnListaUsuario(&$widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		$ID = $_SESSION['user']['ID'];
		return $this->query("SELECT * FROM `widgets-user` WHERE `IDwidget` = '{$widgetID}' AND `IDuser` = '{$ID}';")?true:false;
	}
	
	// Retorna la versión que usa el usuario de un widget dada la ID o el objeto widget retornado por getWidgetsDelUsuario()
	function getWidgetUserVersion(&$WidgetID_o_widgetObject){
		if(!is_array($WidgetID_o_widgetObject)){
			// WidgetID = $WidgetID_o_widgetObject
			$widgetObject = $this->getWidgetDelUsuario($WidgetID_o_widgetObject);
		}
		else{
			$widgetObject = &$WidgetID_o_widgetObject;
		}
		
		if($widgetObject['autoupdate'] === '1'){
			$version = $this->getWidgetDefaultVersion($widgetObject['ID']);
			return $version['version'];
		}
		else{
			return $widgetObject['version'];
		}
	}
	
	function setUserWidgetVersion($widgetID, $widgetVersion){
		$widgetID = mysql_escape_mimic($widgetID);
		if(!isInteger($widgetVersion) || $widgetVersion < 0){
			return false;
		}
		$ID = $_SESSION['user']['ID'];
		
		// Check if the user has the rights to have emy version of the widget, if the version exists and the privileges of the version.
		if(!$this->CanIModifyWidget($widgetID)){
			if(!$this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$widgetVersion}' AND `public` = '1' AND `visible` = '1';")){
				return false;
			}
		}
		else{
			if(!$this->query("SELECT * FROM `widgets-versions` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$widgetVersion}';")){
				return false;
			}
		}
		
		return $this->query("UPDATE `widgets-user` SET `autoupdate` = '0', `version` = '{$widgetVersion}' WHERE `IDuser` = '{$ID}' AND `IDwidget` = '{$widgetID}';");
	}
	
	
	
	
	
	// --------------------------------------------------------
	
	//Cachear resultados. $query es el sql a cachear, $result es el array de la respuesta.
	function cacheResult($query, $result){
		$this->cache[$query] = $result;
	}
	
	//Cachear resultados. $query es el sql a buscar en cache, retorna el array de la respuesta.
	function queryCache($query){
		return isset($this->cache[$query]) ? $this->cache[$query] : false;
	}
}

// Copia de mysql_real_escape_string para uso sin conexión abierta
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

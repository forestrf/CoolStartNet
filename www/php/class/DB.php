<?php

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../functions/generic.php';

// En caso de mudar de base de datos sería necesario modificar la clase siguiente. Las funciones para la aplicación deben permanecer definidas y con los mismos parámetros
// Pero puede variar su contenido para adaptarse a la nueva base de datos
class DB {
	
	// Datos de login por defecto. En caso de necesitar cambiar el login, cambiar aquí
	private $host = MYSQL_HOST;
	private $user = MYSQL_USER;
	private $pass = MYSQL_PASSWORD;
	private $bd   = MYSQL_DATABASE;
	
	private $mysqli;
	
	private $conexionAbierta = false;
	
	var $LAST_MYSQL_ID = '';
	
	private $cache = array();
	
	function Abrir($host=null, $user=null, $pass=null, $bd=null){
		if($host !== null)
			$this->host = $host;
		if($user !== null)
			$this->user = $user;
		if($pass !== null)
			$this->pass = $pass;
		if($bd !== null)
			$this->bd = $bd;
			
		// Conexión persistente:
		// http://www.php.net/manual/en/mysqli.persistconns.php
		// To open a persistent connection you must prepend p: to the hostname when connecting. 
		$this->mysqli = new mysqli('p:'.$this->host, $this->user, $this->pass, $this->bd);
		if ($this->mysqli->connect_errno) {
			echo "Fallo al contectar a MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error;
			return false;
		}
		$this->mysqli->set_charset("utf8");
		return true;
	}
	
	// Realizar una consulta sql. Retorna false en caso de error, además de imprimir el error en pantalla
	// Solo aquí se realiza una consulta directamente. De esta forma se puede abrir conexión en caso de ser necesaria o usar una respuesta cacheada
	private function consulta($query, $cacheable = false){
		if($cacheable){
			$cacheado = $this->consultaCache($query);
			if($cacheado !== false){
				return $cacheado;
			}
		}
		
		if($this->conexionAbierta === false){
			$this->Abrir();
			$this->conexionAbierta = true;
		}
		
		try{
			$resultado = $this->mysqli->query($query, MYSQLI_USE_RESULT);
			if(strpos($query, 'INSERT')!==false){
				$this->LAST_MYSQL_ID = $this->mysqli->insert_id;
			}
			if($resultado === false){
				throw new Exception('Error: '.$this->mysqli->error);
				return false;
			}
			elseif($resultado === true){
				return true;
			}
		}
		catch(Exception $e){
			return false;
		}
		
		$resultadoArray = array();
		while($rt = $resultado->fetch_array(MYSQLI_ASSOC)){$resultadoArray[] = $rt;};
		if($cacheable){
			$this->cacheaResultado($query, $resultadoArray);
		}
		return $resultadoArray;
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# USUARIOS
	#
	# ---------------------------------------------------------------------------
	
	// Consultar si existe un nick en la base de datos
	function existeNick($nick){
		$nick = mysql_escape_mimic($nick);
		return count($this->consulta("SELECT * FROM `usuarios` WHERE `nick` = '{$nick}'", true)) > 0;
	}
	
	// Insertar un usuario en la base de datos
	function insertaUsuario($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = hash_password(mysql_escape_mimic($password));
		$rnd = random_string(32);
		return $this->consulta("INSERT INTO `usuarios` (`nick`, `password`, `RND`) VALUES ('{$nick}', '{$password}', '{$rnd}')") === true;
	}
	
	// Retorna el usuario
	function NickPasswordValidacion($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = hash_password(mysql_escape_mimic($password));
		$result = $this->consulta("SELECT * FROM `usuarios` WHERE `nick` = '{$nick}' AND `password` = '{$password}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS
	#
	# ---------------------------------------------------------------------------
	
	// Retorna la configuración del widget
	function getWidget($nombre){
		$nombre = mysql_escape_mimic($nombre);
		$result = $this->consulta("SELECT * FROM `widgets` WHERE `nombre` = '{$nombre}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Retorna la configuración del widget
	function getWidgetPorID($ID){
		$ID = mysql_escape_mimic($ID);
		$result = $this->consulta("SELECT * FROM `widgets` WHERE `ID` = '{$ID}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Retorna una variable del usuario
	function getVariable($widgetID, $variable, $ID = null){
		$widgetID = mysql_escape_mimic($widgetID);
		$variable = mysql_escape_mimic($variable);
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		$result = $this->consulta("SELECT `valor` FROM `variables` WHERE `IDusuario` = '{$ID}' AND `IDwidget` = '{$widgetID}' AND `variable` = '{$variable}';");
		return count($result) > 0 ? $result[0]['valor'] : false;
	}
	
	// $insert_o_update = 'I' / 'U'
	// No comprueba si la variable está definida. Sin límites
	// POR HACER: Limitar tamaño de lo que se puede guardar
	function setVariable($widgetID, $variable, $valor, $ID = null, $insert_o_update = null){
		$widgetID = mysql_escape_mimic($widgetID);
		$variable = mysql_escape_mimic($variable);
		$valor = mysql_escape_mimic($valor);
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		
		if($insert_o_update === null){
			$result = $this->consulta("SELECT `ID` FROM `variables` WHERE `IDusuario` = '{$ID}' AND `IDwidget` = '{$widgetID}' AND `variable` = '{$variable}';");
			$insert_o_update = $result?'U':'I';
		}
		
		switch($insert_o_update){
			case 'I':
				return $this->consulta("INSERT INTO `variables` (`IDusuario`, `IDwidget`, `variable`, `valor`) VALUES ('{$_SESSION['user']['ID']}', '{$widgetID}', '{$variable}', '{$valor}');");
			break;
			case 'U':
				return $this->consulta("UPDATE `variables` SET `valor` = '{$valor}' WHERE `IDusuario` = '{$_SESSION['user']['ID']}' AND `IDwidget` = '{$widgetID}' AND `variable` = '{$variable}';");
			break;
		}
		
		return false;
	}
	
	// Crear un nuevo widget
	function creaWidget($nombre){
		$nombre = mysql_escape_mimic($nombre);
		$ID = $_SESSION['user']['ID'];
		$result = $this->consulta("SELECT `ID` FROM `widgets` WHERE `nombre` = '{$nombre}';");
		if(!$result){
			return $this->consulta("INSERT INTO `widgets` (`nombre`, `propietarioID`) VALUES ('{$nombre}', '{$ID}');");
		}
		return false;
	}
	
	// Solo se puede borrar widgets públicos si se es admin
	// Borrar un widget es drástico. Borra las variables y lo desenlaza de los usuarios. PELIGROSO
	// No borra el contenido ya que este puede coincidir por hash. El contenido se borrará mediante un proceso rutinario que comprueba la no vinculación de un hash.
	function borraWidget($widgetID){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		if($this->consulta("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `publicado` = '-1';")){
			$this->consulta("DELETE FROM `widgets` WHERE `ID` = '{$widgetID}';");
			$this->consulta("DELETE FROM `variables` WHERE `IDwidget` = '{$widgetID}';");
			$this->consulta("DELETE FROM `widgets-contenido` WHERE `IDwidget` = '{$widgetID}';");
			$this->consulta("DELETE FROM `widgets-usuario` WHERE `IDwidget` = '{$widgetID}';");
			$this->consulta("DELETE FROM `widgets-versiones` WHERE `IDwidget` = '{$widgetID}';");
		}
	}
	
	// Retorna un array con las versiones existentes del widget, de la última a la primera
	function getWidgetVersiones($widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		return $this->consulta("SELECT * FROM `widgets-versiones` WHERE `IDwidget` = '{$widgetID}' ORDER BY `version` DESC;");
	}
	
	// Retorna una de las versiones existentes del widget (la solicitada)
	function getWidgetVersion($widgetID, $version){
		$widgetID = mysql_escape_mimic($widgetID);
		$version = mysql_escape_mimic($version);
		$result = $this->consulta("SELECT * FROM `widgets-versiones` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Retorna la última versión. $publico = true = debe ser público, false = puede o no ser público
	function getWidgetLastVersion($widgetID, $publico = true){
		$widgetID = mysql_escape_mimic($widgetID);
		$publico = $publico?" AND `publico` = '1' ":'';
		$result = $this->consulta("SELECT * FROM `widgets-versiones` WHERE `IDwidget` = '{$widgetID}' {$publico} ORDER BY `version` DESC LIMIT 1;");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// POR TESTEAR
	// Retorna la versión default o de lo contrario la versión pública más avanzada
	function getWidgetDefaultVersion($widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		$version_publica = $this->consulta("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `publicado` > -1;");
		if($version_publica){
			$version_publica = $version_publica[0]['publicado'];
			$version_publica_concreta = $this->consulta("SELECT * FROM `widgets-versiones` WHERE `IDwidget` = '{$widgetID}' AND `publico` = '1' AND `visible` = '1' AND `version` = '{$version_publica}' ORDER BY `version`;");
			if($version_publica_concreta){
				return $version_publica_concreta[0];
			}
			else{
				return getWidgetLastVersion($widgetID, true);
			}
		}
		return false;
	}
	
	// Crear una versión del widget
	function creaWidgetVersion($widgetID){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		$new_version = $this->getWidgetLastVersion($widgetID, false)['version'];
		if(!$new_version){
			$new_version = 0;
		}
		++$new_version;
		return $this->consulta("INSERT INTO `widgets-versiones` (`IDwidget`, `version`) VALUES ('{$widgetID}', '{$new_version}');");
	}
	
	// Publicar una versión del widget (no se puede deshacer)
	function publicaWidgetVersion($widgetID, $version){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		if($this->consulta("SELECT * FROM `widgets-versiones` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';")){
			$this->consulta("UPDATE `widgets-versiones` SET `publico` = '1' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			if($this->consulta("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `publicado` = '-1';")){
				$this->consulta("UPDATE `widgets` SET `publicado` = '{$version}' WHERE `ID` = '{$widgetID}';");
			}
			return true;
		}
		return false;
	}
	
	// Editar comentario de una versión del widget
	function editarWidgetComentario($widgetID, $version, $comentario){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		$comentario = mysql_escape_mimic($comentario);
		return $this->consulta("UPDATE `widgets-versiones` SET `comentario` = '{$comentario}' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
	}
	
	// Borrar una versión no publicada del widget
	function borraWidgetVersion($widgetID, $version){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		if($this->consulta("SELECT * FROM `widgets-versiones` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}' AND `publico` = '0';")){
			$this->consulta("DELETE FROM `widgets-versiones` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			$this->consulta("DELETE FROM `widgets-contenido` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$version}';");
			return true;
		}
		return false;
	}
	
	// Hacer de una versión pública la default
	function versionWidgetDefault($widgetID, $version){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		// Comprobar si existe la versión a hacer default
		if($this->consulta("SELECT * FROM `widgets-versiones` WHERE `IDwidget` = '{$widgetID}' AND `publico` = '1' AND `version` = '{$version}';")){
			return $this->consulta("UPDATE `widgets` SET `publicado` = '{$version}' WHERE `ID` = '{$widgetID}';");
		}
		return false;
	}
	
	// Marcar versión pública como visible o invisible. $visible = true o false
	function versionWidgetVisibilidad($widgetID, $version, $visible){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		if(!isInteger($version) || $version < 0){
			return false;
		}
		$visible = $visible?1:0;
		return $this->consulta("UPDATE `widgets-versiones` SET `visible` = '{$visible}' WHERE `IDwidget` = '{$widgetID}' AND `publico` = '1' AND `version` = '{$version}';");
	}
	
	// Marcar todas las versiones como invisibles
	function ocultarTodasVersionesWidget($widgetID){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		$this->consulta("UPDATE `widgets-versiones` SET `visible` = '0' WHERE `IDwidget` = '{$widgetID}';");
	}
	
	// Version puede ser un número o un array de números (aunque no creo que se use)
	function getWidgetContenidoVersion($widgetID, $version){
		$widgetID = mysql_escape_mimic($widgetID);
		if(!isInteger($version) || $version < 0){
			return false;
		}
		if(is_array($version)){
			$versiones_sql = "version = '".implode("' OR version = '", $version)."'";
			return $this->consulta("SELECT * FROM `widgets-contenido` WHERE `IDwidget` = '{$widgetID}' AND ({$versiones_sql});");
		}
		else{
			return $this->consulta("SELECT * FROM `widgets-contenido` WHERE `IDwidget` = '{$widgetID}' AND version = '{$version}';");
		}
	}
	
	// Guardar archivo para un widget y versión específica. Comprobar antes que se puede subir un archivo para esa versión
	function widgetVersionGuardarArchivo($widgetID, $widgetVersion, $nombre, $tipo, &$content){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		$widgetVersion = mysql_escape_mimic($widgetVersion);
		$nombre = mysql_escape_mimic($nombre);
		$content = mysql_escape_mimic($content);
		$hash = file_hash($content);
		$this->consulta("INSERT INTO `widgets-contenido` (`IDwidget`, `version`, `nombre`, `hash`) VALUES ('{$widgetID}', '{$widgetVersion}', '{$nombre}', '{$hash}');");
		if(!$this->consulta("SELECT * FROM `contenido` WHERE `hash` = '{$hash}';")){
			$this->consulta("INSERT INTO `contenido` (`hash`, `data`, `tipo`) VALUES ('{$hash}', '{$content}', '{$tipo}');");
		}
	}
	
	// Borrar archivo de un widget y versión específica.
	function widgetVersionBorrarArchivo($widgetID, $widgetVersion, $hash){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		$widgetVersion = mysql_escape_mimic($widgetVersion);
		$hash = mysql_escape_mimic($hash);
		return $this->consulta("DELETE FROM `widgets-contenido` WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$widgetVersion}' AND `hash` = '{$hash}';");
	}
	
	// Borrar archivo de un widget y versión específica.
	function widgetVersionRenombraArchivo($widgetID, $widgetVersion, $hash, $nombre){
		if(!$this->tengoControlSobreWidget($widgetID)){
			return false;
		}
		$widgetVersion = mysql_escape_mimic($widgetVersion);
		$hash = mysql_escape_mimic($hash);
		$nombre = mysql_escape_mimic($nombre);
		return $this->consulta("UPDATE `widgets-contenido` SET `nombre` = '{$nombre}' WHERE `IDwidget` = '{$widgetID}' AND `version` = '{$widgetVersion}' AND `hash` = '{$hash}';");
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS - USUARIOS
	#
	# ---------------------------------------------------------------------------
	
	// Retorna un listado con los widgets que tiene el usuario. Si se especifica ID se buscará los widgets del usuario con esa id, de lo contrario se usa el actual usuario.
	function getWidgetsDelUsuario($ID = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		return $this->consulta("SELECT `widgets`.* FROM `widgets-usuario` LEFT JOIN `widgets` ON `widgets-usuario`.`IDwidget` = `widgets`.`ID` WHERE `IDusuario` = '{$ID}'");
	}
	
	// Retorna un listado con los widgets que puede usar el usuario en la página principal.
	function getWidgetsDisponiblesUsuario($ID = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		return $this->consulta("SELECT * FROM `widgets` WHERE `propietarioID` = '-1' OR `propietarioID` = '{$ID}' OR `publicado` > -1;"); // Por poner filtrado de widgets privados
	}
	
	// Retorna un listado con los widgets propiedad del usuario sobre los cuales tiene el control, como borrarlos o editarlos
	// ID puede dejarse null si se llama con $admin = true
	function getWidgetsControlUsuario($ID = null, $admin = false){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		if($admin){
			return $this->consulta("SELECT * FROM `widgets`;");
		}
		else{
			return $this->consulta("SELECT * FROM `widgets` WHERE `propietarioID` = '{$ID}';");
		}
	}
	
	// Retorna true si se puede manipular el widget por el usuario
	function tengoControlSobreWidget(&$widgetID){
		$widgetID = mysql_escape_mimic($widgetID);
		return $this->consulta("SELECT * FROM `widgets` WHERE `ID` = '{$widgetID}' AND `propietarioID` = '{$_SESSION['user']['ID']}'")?true:false;
	}
	
	// Quitar un widget de un usuario no borra la configuraciones del widget de ese usuario.
	function quitarWidgetDelUsuario($widgetID, $ID = null){
		$widgetID = mysql_escape_mimic($widgetID);
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		$this->consulta("DELETE FROM `widgets-usuario` WHERE `IDwidget` = '{$widgetID}' AND `IDusuario` = '{$ID}'");
	}
	
	// Agregar un widget a un usuario.
	function agregarWidgetAlUsuario($widgetID, $ID = null){
		$widgetID = mysql_escape_mimic($widgetID);
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['user']['ID'];
		
		// Comprobar si el usuario ya tenía el widget
		if(count($this->consulta("SELECT * FROM `widgets-usuario` WHERE `IDwidget` = '{$widgetID}' AND `IDusuario` = '{$ID}'")) === 0){
			$this->consulta("INSERT INTO `widgets-usuario` (`IDwidget`, `IDusuario`) VALUES ('{$widgetID}', '{$ID}')");
		}
	}
	
	
	
	
	
	
	
	// --------------------------------------------------------
	
	//Cachear resultados. $consulta es el sql a cachear, $resultado es el array de la respuesta y $tiempoValido es la cantidad de segundos que se guardaré en cache (usando memcache)
	function cacheaResultado($consulta, $resultado){
		$this->cache[$consulta] = $resultado;
	}
	
	//Cachear resultados. $consulta es el sql a cachear, $resultado es el array de la respuesta y $tiempoValido es la cantidad de segundos que se guardaré en cache (usando memcache)
	function consultaCache($consulta){
		return isset($this->cache[$consulta]) ? $this->cache[$consulta] : false;
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

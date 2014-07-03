<?php

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../funciones/genericas.php';

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
	
	// Retorna una variable del usuario
	function getVariable($widgetID, $variable, $ID = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['usuario']['ID'];
		$widgetID = mysql_escape_mimic($widgetID);
		$variable = mysql_escape_mimic($variable);
		$result = $this->consulta("SELECT `valor` FROM `variables` WHERE `IDusuario` = '{$ID}' AND `IDwidget` = '{$widgetID}' AND `variable` = '{$variable}';");
		return count($result) > 0 ? $result[0]['valor'] : false;
	}
	
	// $insert_o_update = 'I' / 'U'
	function setVariable($widgetID, $variable, $valor, $ID = null, $insert_o_update = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['usuario']['ID'];
		$widgetID = mysql_escape_mimic($widgetID);
		$variable = mysql_escape_mimic($variable);
		$valor = mysql_escape_mimic($valor);
		
		if($insert_o_update === null){
			$result = $this->consulta("SELECT `ID` FROM `variables` WHERE `IDusuario` = '{$ID}' AND `IDwidget` = '{$widgetID}' AND `variable` = '{$variable}';");
			$insert_o_update = $result?'U':'I';
		}
		
		switch($insert_o_update){
			case 'I':
				return $this->consulta("INSERT INTO `variables` (`IDusuario`, `IDwidget`, `variable`, `valor`) VALUES ('{$_SESSION['usuario']['ID']}', '{$widgetID}', '{$variable}', '{$valor}');");
			break;
			case 'U':
				return $this->consulta("UPDATE `variables` SET `valor` = '{$valor}' WHERE `IDusuario` = '{$_SESSION['usuario']['ID']}' AND `IDwidget` = '{$widgetID}' AND `variable` = '{$variable}';");
			break;
		}
		
		return false;
	}
	
	function creaWidget($nombre){
		$ID = $_SESSION['usuario']['ID'];
		$nombre = mysql_escape_mimic($nombre);
		$result = $this->consulta("SELECT `ID` FROM `widgets` WHERE `nombre` = '{$nombre}';");
		if(!$result){
			return $this->consulta("INSERT INTO `widgets` (`nombre`, `propietarioID`) VALUES ('{$nombre}', '{$ID}');");
		}
		return false;
	}
	
	// Solo se puede borrar widgets públicos si se es admin
	// Borrar un widget es drástico. Borra las variables y lo desenlaza de los usuarios. PELIGROSO
	// No borra el contenido ya que este puede coincidir por hash. El contenido se borrará mediante un proceso rutinario que comprueba la no vinculación de un hash.
	function borraWidget($widgetID, $admin = false){
		$ID = $_SESSION['usuario']['ID'];
		$widgetID = mysql_escape_mimic($widgetID);
		if($admin){
			$extra_consulta = "AND `propietarioID` = '{$ID}'";
		}
		$this->consulta("DELETE FROM `widgets` WHERE `ID` = '{$widgetID}' {$extra_consulta};");
		$this->consulta("DELETE FROM `variables` WHERE `IDwidget` = '{$widgetID}' {$extra_consulta};");
		$this->consulta("DELETE FROM `widgets-contenido` WHERE `IDwidget` = '{$widgetID}' {$extra_consulta};");
		$this->consulta("DELETE FROM `widgets-usuario` WHERE `IDwidget` = '{$widgetID}' {$extra_consulta};");
		$this->consulta("DELETE FROM `widgets-variables` WHERE `IDwidget` = '{$widgetID}' {$extra_consulta};");
	}
	
	
	
	# ---------------------------------------------------------------------------
	#
	# WIDGETS - USUARIOS
	#
	# ---------------------------------------------------------------------------
	
	// Retorna un listado con los widgets que tiene el usuario. Si se especifica ID se buscará los widgets del usuario con esa id, de lo contrario se usa el actual usuario.
	function getWidgetsDelUsuario($ID = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['usuario']['ID'];
		return $this->consulta("SELECT `widgets`.* FROM `widgets-usuario` LEFT JOIN `widgets` ON `widgets-usuario`.`IDwidget` = `widgets`.`ID` WHERE `IDusuario` = '{$ID}'");
	}
	
	// Retorna un listado con los widgets que puede usar el usuario en la página principal.
	function getWidgetsDisponiblesUsuario($ID = null){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['usuario']['ID'];
		return $this->consulta("SELECT * FROM `widgets`;"); // Por poner filtrado de widgets privados
	}
	
	// Retorna un listado con los widgets propiedad del usuario sobre los cuales tiene el control, como borrarlos o editarlos
	// ID puede dejarse null si se llama con $admin = true
	function getWidgetsControlUsuario($ID = null, $admin = false){
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['usuario']['ID'];
		if($admin){
			return $this->consulta("SELECT * FROM `widgets`;");
		}
		else{
			return $this->consulta("SELECT * FROM `widgets` WHERE `propietarioID` = '{$ID}';");
		}
	}
	
	// Quitar un widget de un usuario no borra la configuraciones del widget de ese usuario.
	function quitarWidgetDelUsuario($widgetID, $ID = null){
		$widgetID = mysql_escape_mimic($widgetID);
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['usuario']['ID'];
		$this->consulta("DELETE FROM `widgets-usuario` WHERE `IDwidget` = '{$widgetID}' AND `IDusuario` = '{$ID}'");
	}
	
	// Agregar un widget a un usuario.
	function agregarWidgetAlUsuario($widgetID, $ID = null){
		$widgetID = mysql_escape_mimic($widgetID);
		$ID = $ID !== null ? mysql_escape_mimic($ID) : $_SESSION['usuario']['ID'];
		
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

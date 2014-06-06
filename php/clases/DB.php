<?php

// En caso de mudar de base de datos sería necesario modificar la clase siguiente. Las funciones para la aplicación deben permanecer definidas y con los mismos parámetros
// Pero puede variar su contenido para adaptarse a la nueva base de datos
class DB {
	
	// Datos de login por defecto. En caso de necesitar cambiar el login, cambiar aquí
	private $host = "localhost";
	private $user = "root";
	private $pass = "";
	private $bd = "forest";

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
		return count($this->consulta("SELECT * FROM usuarios WHERE `nick` = '{$nick}'", true)) > 0;
	}
	
	// Insertar un usuario en la base de datos
	function insertaUsuario($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = hash_password(mysql_escape_mimic($password));
		echo $password;
		return $this->consulta("INSERT INTO usuarios (`nick`, `password`) VALUES ('$nick', '$password')") === true;
	}
	
	// Retorna el usuario
	function NickPasswordValidacion($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = hash_password(mysql_escape_mimic($password));
		$result = $this->consulta("SELECT * FROM usuarios WHERE `nick` = '{$nick}' AND `password` = '{$password}';");
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
		$result = $this->consulta("SELECT * FROM widgets WHERE `nombre` = '{$nombre}';");
		return count($result) > 0 ? $result[0] : false;
	}
	
	// Retorna una variable del usuario
	function getVariable($widgetID, $variable){
		$widgetID = mysql_escape_mimic($widgetID);
		$variable = mysql_escape_mimic($variable);
		$result = $this->consulta("SELECT ID, tipo, valor FROM variables WHERE `IDusuario` = '{$_SESSION['usuario']['ID']}' AND `IDwidget` = '{$widgetID}' AND `variable` = '{$variable}';");
		return count($result) > 0 ? $result[0] : false;
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
	return custom_hmac('md5', $password, 'GVWUIF/WA&Htb9 hwaw&.434/ */34+');
}

function custom_hmac($algo, $data, $key, $raw_output=false){
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

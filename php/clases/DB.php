<?php

// En caso de mudar de base de datos sería necesario modificar la clase siguiente. Las funciones para la aplicación deben permanecer definidas y con los mismos parámetros
// Pero puede variar su contenido para adaptarse a la nueva base de datos
class DB {
	
	// Datos de login por defecto. En caso de necesitar cambiar el login, cambiar aquí
	private $host = "localhost";
	private $user = "root";
	private $pass = "";
	private $bd = "briscaonline";

	private $mysqli;
	
	private $conexionAbierta = false;
	
	var $LAST_MYSQL_ID = '';
	
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
		/*if($cacheable && MEMCACHE){
			$cacheado = $this->consultaCache($query);
			if($cacheado !== false){
				return $cacheado;
			}
		}*/
		
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
		
		/*if($cacheable && MEMCACHE){
			$arrayCacheado = $resultado->fetch_array(MYSQLI_ASSOC);
			$this->cacheaResultado($query, $arrayCacheado);
			return $arrayCacheado;
		}*/
		$resultadoArray = array();
		while($rt = $resultado->fetch_array(MYSQLI_ASSOC)){$resultadoArray[] = $rt;};
		return $resultadoArray;
	}
	
	// FUNCIONES QUE SE USAN EN LA APLICACIÓN
	
	// Consultar si existe un nick en la base de datos
	function existeNick($nick){
		$nick = mysql_escape_mimic($nick);
		return count($this->consulta("SELECT * FROM usuarios WHERE `NICK` = '{$nick}'", true)) > 0;
	}
	
	// Insertar un usuario en la base de datos
	function insertaUsuario($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = mysql_escape_mimic($password);
		return $this->consulta("INSERT INTO usuarios (`Nick`, `Password`) VALUES ('$nick', '$password')") === true;
	}
	
	// Retorna la ID del usuario que tiene ese NICK
	function idDesdeNick($nick){
		$nick = mysql_escape_mimic($nick);
		$nick = $this->consulta("SELECT ID FROM usuarios WHERE NICK = '{$nick}'", true);
		$nick = $nick[0];
		return $nick["ID"];
	}
	
	// Retorna el NICK del usuario dada una ID
	function nickDesdeId($ID){
		$ID = mysql_escape_mimic($ID);
		$ID = $this->consulta("SELECT NICK FROM usuarios WHERE ID = '{$ID}'", true);
		$ID = $ID[0];
		return $ID["NICK"];
	}
	
	// Retorna la ID
	function NickPasswordValidacion($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = mysql_escape_mimic($password);
		return count($this->consulta("SELECT * FROM usuarios WHERE NICK = '{$nick}' AND PASSWORD = '{$password}' AND user_validado = '';")) > 0;
	}
	
	// --------------------------------------------------------
	/*
	//Cachear resultados. $consulta es el sql a cachear, $resultado es el array de la respuesta y $tiempoValido es la cantidad de segundos que se guardaré en cache (usando memcache)
	function cacheaResultado($consulta, $resultado, $tiempoValido = 3600){
		$resultado = json_encode($resultado);
		$memcache_obj = memcache_pconnect('localhost', 11211);
		$memcache_obj->add($consulta, $resultado, false, $tiempoValido);
	}
	
	//Cachear resultados. $consulta es el sql a cachear, $resultado es el array de la respuesta y $tiempoValido es la cantidad de segundos que se guardaré en cache (usando memcache)
	function consultaCache($consulta){
		$memcache_obj = memcache_pconnect('localhost', 11211);
		$resultado = $memcache_obj->get($consulta);
		return $resultado===false?false:json_decode($resultado);
	}
	*/
}

// Copia de mysql_real_escape_string para uso sin conexión abierta
// http://es1.php.net/mysql_real_escape_string
function mysql_escape_mimic($inp){
	if(is_array($inp))
		return array_map(__METHOD__, $inp);

	if(!empty($inp) && is_string($inp)) {
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
	}

	return $inp;
}
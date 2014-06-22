<?php

// MAKE A COPY OF THIS FILE, FILL IT AND RENAME IT AS "config.php"
// HAZ UNA COPIA DE ESTE ARCHIVO, RELLÉNALO Y RENÓMBRALO COMO "config.php"

# MYSQL
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', 'password');
define('MYSQL_DATABASE', 'database_name');

# Semilla para el hash de las contraseñas de los usuarios. Es necesario reiniciar las contraseñas para cambiar esta variable.
# Seed to generate the hash of the user's password. A password reset is necessary to change this variable.
define('USER_PASSWORD_HMAC_SEED', 'write random characters here');

# Semilla para el hash de los token.
# Seed to generate the hash of the token.
define('PASSWORD_TOKEN_IPA', 'write random characters here');

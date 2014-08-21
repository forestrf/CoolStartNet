<?php

// MAKE A COPY OF THIS FILE, FILL IT AND RENAME IT AS "config.php"

# MYSQL
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', 'password');
define('MYSQL_DATABASE', 'database_name');

# Seed to generate the hash of the user's password. A password reset is necessary to change this variable.
define('USER_PASSWORD_HMAC_SEED', 'write random characters here');

# Seed to generate the hash of the token.
define('PASSWORD_TOKEN_IPA', 'write random characters here');

# Seed to generate the hash of the api token.
define('PASSWORD_TOKEN_API', 'write random characters here');

# path to the web with / in the end, starting with the subdomain (if there is) and without protocol.
define('WEB_PATH', 'www.mywebhere.com/folder/to/site/');

# Max file size for the uploaded widget files. In bytes.
define('MAX_FILE_SIZE_BYTES', 512000);

# Max user nickname length
define('NICK_MAX_LENGTH', 15);

# Max user password length
define('PASSWORD_MAX_LENGTH', 30);

# Max filename length
define('FILENAME_MAX_LENGTH', 50);

# Max comment length of widget versions
define('WIDGET_VERSION_COMMENT_MAX_LENGTH', 250);

# Max number of files for a widget version
define('WIDGET_VERSION_MAX_FILES_NUMBER', 50);

# Max user data stored in form of variables. In bytes.
define('USER_MAX_BYTES_STORED_DB', 5242880); // 1MB

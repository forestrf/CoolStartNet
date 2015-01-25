<?php

// MAKE A COPY OF THIS FILE, FILL IT AND RENAME IT AS "config.php"

# MYSQL
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', 'password');
define('MYSQL_DATABASE', 'database_name');

# Seed to generate the hash of the ipa token. A cache reset is necessary to change it.
define('PASSWORD_TOKEN_IPA', 'write random characters here');

# Seed to generate the hash of the api token. A cache reset is necessary to change it.
define('PASSWORD_TOKEN_API', 'write random characters here');

# Max time, in seconds, that a session lasts
define('SESSION_TIME', 2592000); // 30 Days

# Max time, in seconds, that a cached query is saved in ram
define('QUERY_CACHE_TTL', 10800); // 3 Hours

# path to the web with / in the end, starting with the subdomain (if there is) and without protocol.
define('WEB_PATH', 'www.mydomain.com/folder/to/site/');
define('FORUM_WEB_PATH', 'forum.mydomain.com/folder/to/site/');
define('DOMAIN', 'mydomain.com');
define('MAIL_DIRECTION', 'do-not-reply' . DOMAIN);

# Max file size for the uploaded widget files. In bytes.
define('MAX_FILE_SIZE_BYTES', 512000);

# Path to store all the widget files, without / at the start
define('WIDGET_FILES_PATH', 'widget-files/');

# Max user nickname lengths
define('NICK_MAX_LENGTH', 15);
define('PASSWORD_MAX_LENGTH', 30);
define('EMAIL_MAX_LENGTH', 50);

# Max filename length
define('FILENAME_MAX_LENGTH', 50);

# Max comment length of widget versions
define('WIDGET_VERSION_COMMENT_MAX_LENGTH', 250);

# Max number of files for a widget version
define('WIDGET_VERSION_MAX_FILES_NUMBER', 50);

# Max user data stored in form of variables. In bytes.
define('USER_MAX_BYTES_STORED_DB', 5242880); // 5MB

# Windows has a lot of problems. For future conditionals.
define('MACHINE', strtoupper(substr(PHP_OS, 0, 3)));

# Registration
define('USERS_CAN_REGISTER', false);

# Default user (when not logged in)
define('DEFAULT_USER_NICK', 'ANONYMOUS');
define('DEFAULT_USER_PASSWORD', 'ANONYMOUS');

define('GLOBAL_USER_ID', 0);

#Captcha ReCaptcha
define('CAPTCHA_PUBLIC_KEY', 'key here');
define('CAPTCHA_PRIVATE_KEY', 'key here');

#Login attempts
define('MAX_LOGIN_FAILS', 10);
define('LOGIN_FAIL_WAIT', 10); // minutes

# Analytics JS code
define('ANALYTICS_JS', "JS Analytics from GA or other service. Without <script></script>");

# Dropbox
define('DROPBOX_APP_NAME', 'coolstart.net/1.0');
define('DROPBOX_KEY', 'app key');
define('DROPBOX_SECRET', 'app secret');

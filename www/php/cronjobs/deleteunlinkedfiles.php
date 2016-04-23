<style>
body, a {
	background-color:#000;
	color:#fff;
}
.ok {
	color:#0f0;
}
.fail {
	color:#f00;
}
.info {
	color:#ff0;
}
.query {
	color:#0ff;
}
</style>
<?php

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../lib/DB.php';
require_once __DIR__.'/../functions/generic.php';

$db = new DB();

if (isset($_SERVER['HTTP_REFERER']) && preg_match('@^https?://' . WEB_PATH . '@', $_SERVER['HTTP_REFERER'])) {	
	$db->debug_mode(true);
}

$files  = scandir(__DIR__.'/../../'.WIDGET_FILES_PATH);

foreach ($files as $file) {
	var_dump($file);
	if (strlen($file) == WIDGET_FILES_HASH_LENGTH) {
		$exists = $db->check_widget_file_hash($file);
		if (!$exists) {
			unlink(__DIR__.'/../../'.WIDGET_FILES_PATH.$file);
		}
	}
}

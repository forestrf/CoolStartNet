<?php
require_once 'php/functions/generic.php';
$session = open_db_session('session');
$session->stop();
header('Location: //'.WEB_PATH, true, 302);
?>
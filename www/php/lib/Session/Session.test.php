<?php

require '../DB.php';
require 'Session.php';



$result = array();



$db = new DB();
$db->debug_mode(true);
$db->debug_to_array(true);

$session = new Session($db);

$result[] = $session->exists();
$result[] = $session->userID;

$session->create_session(8);

$result[] = $session->exists();
$result[] = $session->userID;

$result[] = $db->debug_array;

if(isset($_GET['del']))
	$session->remove_session();




echo '<pre>';
var_dump($result);

var_dump($_COOKIE);

<?php

//header('Content-Type: text/html; charset=UTF-8');

require_once 'php/functions/generic.php';
$db = open_db_session();

require_once 'php/lib/rainmeter/Rainmeter.php';

$rainmeter = new Rainmeter();

$rainmeter->set_tmp_path('tmp');

$rainmeter->unpack_skin('../rainmeter tests/Zero_TwentyThree_1.6.rmskin');

$list = $rainmeter->get_ini_list();
//var_dump($list);

foreach($list as $elem) {
	$rainmeter->generate_widget($elem, '../widgets', str_replace('/', '_', $elem));
}


?>
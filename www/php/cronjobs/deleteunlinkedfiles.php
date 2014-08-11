<?php

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../class/DB.php';
require_once __DIR__.'/../functions/generic.php';

$db = new DB();

$db->delete_unlinked_files();
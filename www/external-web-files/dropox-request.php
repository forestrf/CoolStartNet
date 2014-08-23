<?php
require_once '../php/config.php';
require_once 'dropbox-functions.php';

session_start();

$authorizeUrl = getWebAuth()->start();
header("Location: $authorizeUrl");

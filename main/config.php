<?php
/*
** Config file. Allow to change easily the DB to another place/configuration
*/


$db_login = 'xxx';
$db_pass = 'xxx';
$db_server = 'xxx';
$db = 'xxx';
$db_port = '';

define('ROOT', './');
define('CURRENT_TIME', time());

define('COOKIE_NAME', 'tempo');
define('COOKIE_PATH', './');
define('COOKIE_DOMAIN', '');
define('OS_SERVER', (preg_match('/^WIN/', PHP_OS)) ? 'windows' : 'unix');

?>

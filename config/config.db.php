<?php
require('config/config.inc.php');
if (file_exists('config/config_override.php')) {
	require('config/config_override.php');
}
$dbConfig['base'] = [
	'db_server' => $dbconfig['db_server'],
	'db_port' => $dbconfig['db_port'],
	'db_username' => $dbconfig['db_username'],
	'db_password' => $dbconfig['db_password'],
	'db_name' => $dbconfig['db_name'],
	'db_type' => $dbconfig['db_type'],
];
$dbConfig['admin'] = [
	'db_server' => '_SERVER_',
	'db_port' => '_PORT_',
	'db_username' => '_USERNAME_',
	'db_password' => '_PASSWORD_',
	'db_name' => '_NAME_',
	'db_type' => '_TYPE_',
];
$dbConfig['log'] = [
	'db_server' => '_SERVER_',
	'db_port' => '_PORT_',
	'db_username' => '_USERNAME_',
	'db_password' => '_PASSWORD_',
	'db_name' => '_NAME_',
	'db_type' => '_TYPE_',
];
$dbConfig['portal'] = [
	'db_server' => '_SERVER_',
	'db_port' => '_PORT_',
	'db_username' => '_USERNAME_',
	'db_password' => '_PASSWORD_',
	'db_name' => '_NAME_',
	'db_type' => '_TYPE_',
];

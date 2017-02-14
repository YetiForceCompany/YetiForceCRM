<?php
/*
 * Supports databases:
 * pgsql => PostgreSQL
 * mysqli => MySQL
 * mysql =>  MySQL
 * sqlite => sqlite 3
 * sqlite2 => sqlite 2
 * sqlsrv => newer MSSQL driver on MS Windows hosts
 * mssql => older MSSQL driver on MS Windows hosts
 * dblib => dblib drivers on GNU/Linux (and maybe other OSes) hosts
 * cubrid => CUBRID
 * oci => Oracle driver
 * 
  'admin' => [
  'host' => '',
  'port' => '',
  'username' => '',
  'password' => '',
  'name' => '',
  'charset' => 'utf8'
  ],
  'log' => [
  'host' => '',
  'port' => '',
  'username' => '',
  'password' => '',
  'name' => '',
  'charset' => 'utf8'
  ],
  'webservice' => [
  'host' => '',
  'port' => '',
  'username' => '',
  'password' => '',
  'name' => '',
  'charset' => 'utf8'
  ],
 */
$dbconfig = AppConfig::main('dbconfig');
return [
	'base' => [
		'dsn' => $dbconfig['db_type'] . ':host=' . $dbconfig['db_server'] . ';dbname=' . $dbconfig['db_name'] . ';port=' . $dbconfig['db_port'],
		'host' => $dbconfig['db_server'],
		'port' => $dbconfig['db_port'],
		'username' => $dbconfig['db_username'],
		'password' => $dbconfig['db_password'],
		'dbName' => $dbconfig['db_name'],
		'tablePrefix' => 'yf_',
		'charset' => 'utf8'
	]
];

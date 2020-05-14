<?php
/**
 * Post-installation script for Docker.
 */
$path = '/etc/mysql/debian.cnf';
if (file_exists($path)) {
	$content = str_replace('password = ', 'password = ' . getenv('DB_ROOT_PASS'), file_get_contents($path));
	file_put_contents($path, $content);
}

$path = '/etc/php/' . getenv('PHP_VER') . '/fpm/pool.d/www.conf';
if (file_exists($path)) {
	$conf = PHP_EOL . ';####' . PHP_EOL . ';# Best FastCGI Process Manager configuration for YetiForceCRM' . PHP_EOL . ';####' . PHP_EOL . PHP_EOL .
	'env[PROVIDER] = docker' . PHP_EOL .
	'php_admin_value[open_basedir] = /var/www/html/:/tmp/:/var/tmp/:/etc/nginx/ssl/:/etc/ssl/' . PHP_EOL .
	'clear_env = no' . PHP_EOL .
	'request_terminate_timeout = 600' . PHP_EOL .
	'pm.max_requests = 5000' . PHP_EOL .
	'pm.process_idle_timeout = 600s;';
	file_put_contents($path, $conf, FILE_APPEND);
}

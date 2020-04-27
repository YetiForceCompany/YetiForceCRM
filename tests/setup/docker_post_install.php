<?php
/**
 * Post-installation script for Docker.
 */
$path = '/etc/mysql/debian.cnf';
if (file_exists($path)) {
	$content = str_replace('password = ', 'password = ' . getenv('DB_ROOT_PASS'), file_get_contents($path));
	file_put_contents($path, $content);
}

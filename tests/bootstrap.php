<?php
/**
 * Travis CI test script.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
chdir(__DIR__ . '/../');
set_include_path(getcwd());
define('ROOT_DIRECTORY', getcwd());

if (!class_exists('Vtiger_WebUI')) {
	require_once 'include/main/WebUI.php';
}
\App\Config::$requestMode = 'TEST';

//fix phpunit console for windows
if (!getenv('ANSICON')) {
	putenv('ANSICON=80');
}

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	define('IS_WINDOWS', true);
} else {
	define('IS_WINDOWS', false);
}
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');
ini_set('error_log', ROOT_DIRECTORY . 'cache/logs/phpError.log');
ini_set('output_buffering', 'On');
ini_set('max_execution_time', 600);
ini_set('default_socket_timeout', 600);
ini_set('post_max_size', '200M');
ini_set('upload_max_filesize', '200M');
ini_set('max_input_vars', 10000);
ini_set('xdebug.enable', 'On');
App\Session::init();

if (IS_WINDOWS) {
	App\User::setCurrentUserId(1);
}

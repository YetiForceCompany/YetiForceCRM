<?php
/**
 * Travis CI test script.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$installDatabase = true;
chdir(__DIR__ . '/../');
set_include_path(getcwd());
\define('ROOT_DIRECTORY', getcwd());

if (!class_exists('Vtiger_WebUI')) {
	require_once 'include/main/WebUI.php';
}
\App\Process::$requestMode = 'TEST';

//fix phpunit console for windows
if (!getenv('ANSICON')) {
	putenv('ANSICON=80');
}

if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
	\define('IS_WINDOWS', true);
} else {
	\define('IS_WINDOWS', false);
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
if (empty($_SERVER['YETI_MAIL_PASS'])) {
	echo 'No mailbox password provided, please set YETI_MAIL_PASS in $_SERVER array' . PHP_EOL;
}
if ($installDatabase) {
	echo 'Installing test database ...' . PHP_EOL;
	require_once 'install/models/InitSchema.php';

	$_SESSION['config_file_info']['currency_name'] = 'Poland, Zlotych';
	$_SESSION['config_file_info']['currency_code'] = 'PLN';
	$_SESSION['config_file_info']['currency_symbol'] = 'zł';

	$initSchema = new \Install_InitSchema_Model();
	$initSchema->initialize();
	if (!($_SESSION['installation_success'] ?? false)) {
		echo 'Some exceptions occurred in database install queries, verify if database was empty before run.' . PHP_EOL;
	}
} else {
	echo 'Skipped test database install ...' . PHP_EOL;
}

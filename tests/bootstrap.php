<?php
/**
 * Travis CI test script.
 *
 * @package Tests
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
chdir(__DIR__ . '/../');
set_include_path(getcwd());
\define('ROOT_DIRECTORY', getcwd());

if (!class_exists('Vtiger_WebUI')) {
	require_once 'include/main/WebUI.php';
}

$installDatabase = true;
$debug = true;
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
App\Session::init();

if (IS_WINDOWS) {
	App\User::setCurrentUserId(1);
}
if (empty($_SERVER['YETI_MAIL_PASS'])) {
	echo 'No mailbox password provided, please set YETI_MAIL_PASS in $_SERVER array' . PHP_EOL;
}
if (empty($_SERVER['YETI_TEST_MODULE_KEY'])) {
	echo 'TestData package not available, no sample data to install.' . PHP_EOL;
}
if ($installDatabase) {
	$startTime = microtime(true);
	echo 'Installing test database ';
	require_once 'install/models/InitSchema.php';

	$_SESSION['config_file_info']['currency_name'] = 'Poland, Zlotych';
	$_SESSION['config_file_info']['currency_code'] = 'PLN';
	$_SESSION['config_file_info']['currency_symbol'] = 'zł';

	$initSchema = new \Install_InitSchema_Model();
	$initSchema->initialize();
	echo round(microtime(true) - $startTime, 5) . PHP_EOL;
	if (!($_SESSION['installation_success'] ?? false)) {
		echo 'Some exceptions occurred in database install queries, verify if database was empty before run.' . PHP_EOL;
	}
} else {
	echo 'Skipped test database install ...' . PHP_EOL;
}
if ($debug) {
	echo 'error_reporting: ' . error_reporting() . ' | ' . \App\Utils::varExport(\App\ErrorHandler::error2string(error_reporting()), true) . PHP_EOL;
	echo 'error_log: ' . ini_get('error_log') . PHP_EOL;
	echo 'log_errors: ' . ini_get('log_errors') . PHP_EOL;
	echo 'max_execution_time: ' . ini_get('max_execution_time') . PHP_EOL;
	echo 'display_errors: ' . ini_get('display_errors') . PHP_EOL;
	echo 'disable_functions: ' . ini_get('disable_functions') . PHP_EOL;
	echo 'xdebug.enable: ' . ini_get('xdebug.enable') . PHP_EOL;
	echo 'cgi.fix_pathinfo: ' . ini_get('cgi.fix_pathinfo') . PHP_EOL;
}

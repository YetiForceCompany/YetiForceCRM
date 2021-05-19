<?php
/**
 * phpunit bootstrap script.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @codeCoverageIgnore
 */
chdir(__DIR__ . '/../../');

set_include_path(getcwd());
if (!\defined('ROOT_DIRECTORY')) {
	\define('ROOT_DIRECTORY', getcwd());
}
if (!class_exists('Vtiger_WebUI')) {
	require_once 'include/main/WebUI.php';
}

$installDatabase = getenv('YETI_INSTALLED') ? false : true;
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
	echo round(microtime(true) - $startTime, 1) . ' sec.' . PHP_EOL;
	if (!($_SESSION['installation_success'] ?? false)) {
		echo 'Some exceptions occurred in database install queries, verify if database was empty before run.' . PHP_EOL;
	}
} else {
	echo 'Skipped test database install ...' . PHP_EOL;
}

$id = (new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['user_name' => 'demo'])->scalar();
\App\User::setCurrentUserId($id ?: \Users::getActiveAdminId());

\App\Cron::$confReportIsActive = false;
\App\Cron::$registerIsActive = false;
\App\Cron::$watchdogIsActive = false;
\App\Cron::$shopIsActive = false;

if (\extension_loaded('pcov') && ini_get('pcov.enabled')) {
	echo 'Coverage driver: PCOV - ' . phpversion('pcov') . PHP_EOL;
}
if (\extension_loaded('xdebug') && ini_get('xdebug.enable')) {
	echo 'Coverage driver: Xdebug - ' . phpversion('xdebug') . PHP_EOL;
}

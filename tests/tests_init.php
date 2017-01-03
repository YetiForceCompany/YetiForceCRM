<?php
/**
 * Travis CI test script
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
chdir(dirname(__FILE__) . '/../');

$startTime = microtime(true);
define('REQUEST_MODE', 'TEST');
define('ROOT_DIRECTORY', getcwd());

if (!class_exists('Vtiger_WebUI')) {
	require_once 'include/main/WebUI.php';
}

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');
ini_set('error_log', ROOT_DIRECTORY.'cache/logs/phpError.log');
ini_set('output_buffering', 'On');
ini_set('max_execution_time', 600);
ini_set('default_socket_timeout', 600);
ini_set('post_max_size', '200M');
ini_set('upload_max_filesize', '200M');
ini_set('max_input_vars', 10000);
ini_set('xdebug.enable', 'On');

Vtiger_Session::init();

define('INSTALLATION_MODE_DEBUG', true);
vglobal('Vtiger_Utils_Log', true);

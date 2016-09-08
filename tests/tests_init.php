<?php

/**
 * Travis CI test script
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
chdir(dirname(__FILE__) . '/../');

$startTime = microtime(true);
define('REQUEST_MODE', 'TEST');
define('ROOT_DIRECTORY', getcwd());

if (!class_exists('Vtiger_WebUI')) {
    require_once 'include/main/WebUI.php';
}
Vtiger_Session::init();
$_SERVER['HTTP_X_REQUESTED_WITH'] = true;
echo PHP_EOL;

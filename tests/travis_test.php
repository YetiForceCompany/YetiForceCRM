<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
chdir(dirname(__FILE__) . '/../');

try {
	ob_start();

	$startTime = microtime(true);
	define('REQUEST_MODE', 'WebUI');
	define('ROOT_DIRECTORY', __DIR__);

	require 'include/main/WebUI.php';

	$webUI = new Vtiger_WebUI();
	$webUI->process(AppRequest::init());

	ob_end_clean();
} catch (\Exception $e) {
	echo "\nINSTALLATION FAILED! file: " . $e->getFile() . " - line: " . $e->getLine()
	. "\n" . $e->getMessage()
	. "\n" . str_repeat("-", 120)
	. "\n" . print_r($e->getTrace(), true)
	. "\n" . str_repeat("-", 120)
	. "\n";
}
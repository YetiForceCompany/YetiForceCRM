<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
chdir(dirname(__FILE__) . '/../');

try {
	ob_start();

	$startTime = microtime(true);
	define('REQUEST_MODE', 'WebUI');
	define('ROOT_DIRECTORY', getcwd());

	require 'include/main/WebUI.php';

	$webUI = new Vtiger_WebUI();
	$webUI->process(AppRequest::init());


	$rekord = Vtiger_Record_Model::getCleanInstance('Accounts');
	$rekord->set('accountname', 'YetiForce Sp. z o.o.');
	$rekord->set('assigned_user_id', 1);
	$rekord->set('legal_form', 'PLL_GENERAL_PARTNERSHIP');
	$rekord->save();
	$rekord->isEditable();
	$rekord->isWatchingRecord();
	$rekord->set('accounttype', 'Customer');
	$rekord->set('mode', 'edit');
	$rekord->save();
	$rekord->delete();

	ob_end_clean();
} catch (\Exception $e) {
	echo "\nINSTALLATION FAILED! file: " . $e->getFile() . " - line: " . $e->getLine()
	. "\n" . $e->getMessage()
	. "\n" . str_repeat("-", 120)
	. "\n" . print_r($e->getTrace(), true)
	. "\n" . str_repeat("-", 120)
	. "\n";
}
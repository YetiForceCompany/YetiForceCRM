<?php
/**
 * Basic file to handle files.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
chdir(__DIR__);
require_once __DIR__ . '/include/main/WebUI.php';
require_once __DIR__ . '/include/RequirementsValidation.php';

\App\Process::$requestMode = 'File';
try {
	$webUI = new App\Main\File();
	$webUI->process(App\Request::init());
} catch (Exception $e) {
	\App\Log::error($e->getMessage() . ' => ' . $e->getFile() . ':' . $e->getLine());
	$response = new \Vtiger_Response();
	$response->setEmitType(\Vtiger_Response::$EMIT_JSON);
	$trace = '';
	if (\App\Config::debug('DISPLAY_EXCEPTION_BACKTRACE') && \is_object($e)) {
		$trace = str_replace(ROOT_DIRECTORY . DIRECTORY_SEPARATOR, '', $e->getTraceAsString());
	}
	$response->setError($e->getCode(), $e->getMessage(), $trace);
	$response->emit();
}

<?php
/**
 * Basic file to handle files.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require __DIR__ . '/include/main/WebUI.php';
\App\Process::$requestMode = 'File';
try {
	$webUI = new App\Main\File();
	$webUI->process(App\Request::init());
} catch (Exception $e) {
	\App\Log::error($e->getMessage() . ' => ' . $e->getFile() . ':' . $e->getLine());
	$response = new \Vtiger_Response();
	$response->setEmitType(\Vtiger_Response::$EMIT_JSON);
	$trace = '';
	if (\AppConfig::debug('DISPLAY_EXCEPTION_BACKTRACE') && is_object($e)) {
		$trace = str_replace(ROOT_DIRECTORY . DIRECTORY_SEPARATOR, '', $e->getTraceAsString());
	}
	$response->setHeader(\App\Request::_getServer('SERVER_PROTOCOL') . ' ' . $e->getCode() . ' Internal Server Error');
	$response->setError($e->getCode(), $e->getMessage(), $trace);
	$response->emit();
}

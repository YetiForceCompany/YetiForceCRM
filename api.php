<?php
/**
 * Api base file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require __DIR__ . '/include/ConfigUtils.php';
\App\Process::$startTime = microtime(true);
\App\Process::$requestMode = 'WebApi';
try {
	$api = new \App\Controller\WebApi();
	$api->process();
} catch (Throwable $e) {
	\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString(), 'WebApi');
	$response = new \App\Response();
	$response->setException($e);
	$response->emit();
}

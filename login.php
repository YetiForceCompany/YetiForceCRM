<?php

/**
 * Login base file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once 'include/ConfigUtils.php';
\App\Process::$startTime = microtime(true);
\App\Process::$requestMode = 'WebApi';
try {
	$login = new \App\Controller\Login();
	$login->process();
} catch (Throwable $e) {
	\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString(), 'WebApiLogin');
	$response = new \App\Response();
	$response->setError($e);
	$response->emit();
}

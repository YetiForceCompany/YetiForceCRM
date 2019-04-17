<?php
/**
 * Index file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once 'include/ConfigUtils.php';
\App\Process::$startTime = microtime(true);
\App\Process::$requestMode = 'WebUI';

try {
	$webUI = new \App\Controller\WebUI();
	$webUI->process();
	$env = $webUI->getEnv();
} catch (Throwable $e) {
	\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString(), 'WebUI');
	$e = \App\ErrorHandler::parseException($e);
	http_response_code($e['code']);
	$env = \App\Json::encode(['exception' => $e]);
}
require ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'index.php';

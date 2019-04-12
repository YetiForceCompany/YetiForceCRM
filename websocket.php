<?php
/**
 * Web socket file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once __DIR__ . '/vendor/autoload.php';

try {
	\App\Log::$showLog = \Config\Debug::$websocketShowLog;
	(new \App\Controller\WebSocket())->process();
} catch (Throwable $e) {
	\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString(), 'WebSocket');
	throw $e;
}

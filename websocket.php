<?php
/**
 * Web socket file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require __DIR__ . '/include/ConfigUtils.php';

try {
	\App\Log::$showLog = \App\Config::debug('WEBSOCKET_SHOW_LOG');
	(new \App\Controller\WebSocket())->process();
} catch (Throwable $e) {
	\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString(), 'WebSocket');
	throw $e;
}

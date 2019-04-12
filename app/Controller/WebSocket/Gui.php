<?php
/**
 * Gui container web socket file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\WebSocket;

use App\Process;

/**
 * Gui container web socket class.
 */
class Gui extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		//return \App\User::isLoggedIn();
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		try {
			require_once __DIR__ . '/../../../include/ConfigUtils.php';

			Process::$requestMode = 'WebSocket';
			Process::$processType = 'Actions';
			Process::$processName = $this->request->getByType('action', 'Alnum');

			$handlerClass = \App\Loader::getComponentClassName(Process::$processType, Process::$processName, $this->request->getModule(false));
			$response = new \App\Response();
			$response->setWebSocketServer($this->webSocket->server, $this->frame->fd);
			$handler = new $handlerClass($this->request, $response);
			if ('socket' !== $handler->allowedProtocol && 'mix' !== $handler->allowedProtocol) {
				throw new \App\Exceptions\InvalidProtocol('ERR_INVALID_PROTOCOL', 400);
			}
			if ($handler->loginRequired && !\App\User::isLoggedIn()) {
				throw new \App\Exceptions\Unauthorized('ERR_LOGIN_IS_REQUIRED', 401);
			}
			$handler->preProcess();
			if ($handler->checkPermission()) {
				$handler->process();
			}
			$handler->postProcess();
			$handler->response->emit();
		} catch (\Throwable $e) {
			echo $e->__toString();
			\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
		}
	}
}

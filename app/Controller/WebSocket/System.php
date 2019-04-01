<?php
/**
 * System container web socket file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\WebSocket;

/**
 * System container web socket class.
 */
class System extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$action = $this->request->getByType('action');
		if (\method_exists($this, $action)) {
			$this->{$action}();
		} else {
			$this->pushRaw('Action not found');
		}
	}

	/**
	 * Web socket shutdown function.
	 *
	 * @return void
	 */
	public function shutdown()
	{
		$this->webSocket->server->shutdown();
		$this->pushRaw('Turning off');
	}
}

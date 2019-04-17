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
		if (\Config\Main::$application_unique_key !== $this->request->getByType('application_unique_key', 'alnum')) {
			throw new \App\Exceptions\WebSocketUnauthorized('Unauthorized');
		}
		$action = '_' . $this->request->getByType('action');
		if (\method_exists($this, $action)) {
			if (!$this->request->isEmpty('arguments')) {
				call_user_func_array([$this, $action], $this->request->getArray('arguments'));
			} else {
				$this->{$action}();
			}
		} else {
			$this->pushRaw('Action not found');
		}
	}

	/**
	 * This function supports the call static methods on the side WebSocket.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return void
	 */
	public static function __callStatic(string $name, $arguments = [])
	{
		return \App\WebSocket::getInstance('System')->send(\App\Json::encode([
			'application_unique_key' => \Config\Main::$application_unique_key,
			'action' => $name,
			'arguments' => $arguments
		]), true);
	}

	/**
	 * Web socket shutdown function.
	 *
	 * @return void
	 */
	public function _shutdown()
	{
		$this->webSocket->server->shutdown();
		$this->webSocket->server->push($this->frame->fd, 'Turning off');
	}

	/**
	 * Web socket reload function.
	 *
	 * @return void
	 */
	public function _reload()
	{
		$this->webSocket->server->reload();
		$this->webSocket->server->push($this->frame->fd, 'Resets all the workers');
	}

	/**
	 *  Web socket test function.
	 *
	 * @return void
	 */
	public function _test()
	{
		$this->webSocket->server->push($this->frame->fd, 'test');
	}

	/**
	 * Get all connections.
	 *
	 * @param string $container
	 *
	 * @return void
	 */
	public function _getConnections(string $container = 'Gui')
	{
		$connections = $this->webSocket->getConnections('container', $container);
		unset($connections[$this->frame->fd]);
		$this->webSocket->server->push($this->frame->fd, \App\Json::encode($connections));
	}

	/**
	 * Send data to all connections.
	 *
	 * @param array  $data
	 * @param string $container
	 *
	 * @return void
	 */
	public function _sendToAll(array $data, string $container = 'Gui')
	{
		$i = 0;
		$connections = $this->webSocket->getConnections('container', $container);
		unset($connections[$this->frame->fd]);
		foreach ($connections as $fd => $conn) {
			++$i;
			$this->webSocket->server->push($fd, \App\Json::encode($data));
		}
		$this->webSocket->server->push($this->frame->fd, $i);
	}
}

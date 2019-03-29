<?php
/**
 * Base container web socket controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\WebSocket;

/**
 * Base container web socket controller class.
 */
abstract class Base
{
	/**
	 * WebSocket instance.
	 *
	 * @var \App\Controller\WebSocket
	 */
	private $webSocket;
	/**
	 * WebSocket Frame.
	 *
	 * @var \Swoole\WebSocket\Frame
	 */
	private $frame;
	/**
	 * Request instance.
	 *
	 * @var \App\Request
	 */
	public $request;

	/**
	 * Container constructor.
	 *
	 * @param \App\Controller\WebSocket $webSocket
	 * @param \Swoole\WebSocket\Frame   $frame
	 */
	public function __construct(\App\Controller\WebSocket $webSocket, \Swoole\WebSocket\Frame $frame)
	{
		$this->webSocket = $webSocket;
		$this->frame = $frame;
		$this->request = new \App\Request(\App\Json::decode($frame->data));
	}

	/**
	 * Process function.
	 *
	 * @return void
	 */
	abstract public function process();

	/**
	 * Get frame function.
	 *
	 * @return \Swoole\WebSocket\Frame
	 */
	public function getFrame(): \Swoole\WebSocket\Frame
	{
		return $this->frame;
	}

	/**
	 * Push raw message function.
	 *
	 * @param mixed $message
	 *
	 * @return void
	 */
	public function pushRaw($message)
	{
		$this->webSocket->server->push($this->frame->fd, $message);
		\App\Log::info("PushRaw response | fd: {$this->frame->fd} | Content: " . $message, 'WebSocket');
	}

	/**
	 * Push message function.
	 *
	 * @param mixed $message
	 *
	 * @return void
	 */
	public function push($message)
	{
		$this->webSocket->server->push($this->frame->fd, \App\Json::encode($message));
		\App\Log::info("Push response | fd: {$this->frame->fd} | Content: " . \App\Json::encode($message), 'WebSocket');
	}
}

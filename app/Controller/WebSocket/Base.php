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
	protected $webSocket;
	/**
	 * WebSocket Frame.
	 *
	 * @var \Swoole\WebSocket\Frame
	 */
	protected $frame;
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
	 */
	public function __construct(\App\Controller\WebSocket $webSocket)
	{
		$this->webSocket = $webSocket;
	}

	/**
	 * Set frame.
	 *
	 * @param \Swoole\WebSocket\Frame $frame
	 *
	 * @return void
	 */
	public function setFrame(\Swoole\WebSocket\Frame $frame)
	{
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
	 * Check container permission function.
	 *
	 * @return bool
	 */
	abstract public function checkPermission();
}

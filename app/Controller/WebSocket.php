<?php
/**
 * Web socket controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller;

use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * Web socket controller class.
 */
class WebSocket
{
	/**
	 * Swoole websocket server instance.
	 *
	 * @see https://github.com/swoole/swoole-src
	 *
	 * @var \Swoole\WebSocket\Server
	 */
	public $server;
	/**
	 * Web socket container.
	 *
	 * @var string
	 */
	private $container;
	/**
	 * Connections.
	 *
	 * @var \Swoole\Table
	 */
	private $connections;

	/**
	 * Connect function.
	 *
	 * @param string $host
	 * @param int    $port
	 * @param array  $settings
	 *
	 * @see https://github.com/swoole/swoole-src
	 *
	 * @return void
	 */
	public function connect(string $host = '', int $port = 9000, array $settings = [])
	{
		if (!$host) {
			$host = \Config\WebSocket::$host;
			$port = \Config\WebSocket::$port;
		}
		$this->server = new \Swoole\WebSocket\Server($host, $port);
		$this->server->set(\array_merge([
			'buffer_output_size' => \Config\WebSocket::$bufferOutputSize,
			'pipe_buffer_size' => \Config\WebSocket::$pipeBufferSize,
			'log_file' => __DIR__ . '/../../' . \Config\Debug::$websocketLogFile,
			'log_level' => \Config\Debug::$websocketLogLevel,
		], \Config\WebSocket::$customConfiguration, $settings));
	}

	/**
	 * Requirements validation function.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	private function requirementsValidation()
	{
		if (version_compare(PHP_VERSION, '7.1', '<')) {
			throw new \App\Exceptions\AppException('Wrong PHP version, recommended version >= 7.1');
		}
		if (!\App\Config::main('application_unique_key', false)) {
			throw new \App\Exceptions\AppException('CRM is not installed');
		}
		if (!class_exists('Swoole\WebSocket\Server')) {
			throw new \App\Exceptions\AppException('Swoole is not installed');
		}
	}

	/**
	 * Process function.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function process()
	{
		$this->requirementsValidation();
		\App\Session::init();
		$this->connect();
		$this->loadSharedMemory();

		$this->server->on('start', [$this, 'onStart']);
		$this->server->on('shutdown', [$this, 'onShutdown']);

		$this->server->on('workerStart', [$this, 'onWorkerStart']);
		$this->server->on('workerStop', [$this, 'onWorkerStop']);
		$this->server->on('workerError', [$this, 'onWorkerError']);

		$this->server->on('connect', [$this, 'onConnect']);
		$this->server->on('open', [$this, 'onOpen']);
		$this->server->on('message', [$this, 'onMessage']);
		$this->server->on('close', [$this, 'onClose']);
		$this->server->on('request', [$this, 'onRequest']);

		$this->server->start();
	}

	/**
	 * Load shared memory.
	 *
	 * @return void
	 */
	public function loadSharedMemory()
	{
		$this->connections = new \swoole_table(1024);
		$this->connections->column('fd', \swoole_table::TYPE_INT, 6);
		$this->connections->column('container', \swoole_table::TYPE_STRING, 10);
		$this->connections->column('ip', \swoole_table::TYPE_STRING, 45);
		$this->connections->column('user', \swoole_table::TYPE_INT, 4);
		$this->connections->column('time', \swoole_table::TYPE_INT, 5);
		$this->connections->column('activeRoute', \swoole_table::TYPE_STRING, 100);
		$this->connections->create();
	}

	/**
	 * Connecting function.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 * @param int                      $fd
	 * @param int                      $fromId
	 *
	 * @return void
	 */
	public function onConnect(Server $server, int $fd, int $fromId)
	{
		\App\Log::info("Connecting | fd: {$fd} | fromId: $fromId", 'WebSocket');
	}

	/**
	 * Open the connection function.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 * @param \Swoole\Http\Request     $request
	 *
	 * @return void
	 */
	public function onOpen(Server $server, \Swoole\Http\Request $request)
	{
		\App\Log::info("Open the connection | fd: {$request->fd} | path: {$request->server['path_info']}", 'WebSocket');
		$path = \explode('/', $request->server['path_info']);
		$this->container = \array_pop($path);
		if (!\class_exists($this->getContainerClass($request->fd))) {
			\App\Log::error('Web socket container does not exist: ' . $this->container, 'WebSocket');
			$server->close($request->fd);
		}
		$this->connections[$request->fd] = [
			'fd' => $request->fd,
			'container' => $this->container,
			'ip' => '127.0.0.1' !== $request->server['remote_addr'] ? $request->server['remote_addr'] : ($request->header['x-real-ip'] ?? $request->header['x-forwarded-for']),
			'user' => 0,
			'time' => time()
		];
		$container = $this->getContainer($request->fd);
		if (\method_exists($container, 'onOpen')) {
			$container->onOpen($request, $this->connections[$request->fd]);
		}
	}

	/**
	 * Message function.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 * @param \Swoole\WebSocket\Frame  $frame
	 *
	 * @return void
	 */
	public function onMessage(Server $server, Frame $frame)
	{
		try {
			\App\Log::info("Request message | fd: {$frame->fd} | Content: {$frame->data}", 'WebSocket');
			require_once __DIR__ . '/../../include/ConfigUtils.php';
			\App\Process::$requestMode = 'WebSocket';

			$container = $this->getContainer($frame->fd);
			$container->setFrame($frame);
			if ($container->checkPermission()) {
				$container->process();
			}
		} catch (\Throwable $e) {
			\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString(), 'WebSocket');
		}
	}

	/**
	 * Closing the connection function.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 * @param int                      $fd
	 * @param int                      $fromId
	 *
	 * @return void
	 */
	public function onClose(Server $server, int $fd, int $fromId)
	{
		unset($this->connections[$fd]);
		\App\Log::info("Closing the connection | fd: {$fd} | fromId: $fromId", 'WebSocket');
	}

	/**
	 * Swoole server starting function.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 *
	 * @return void
	 */
	public function onStart(Server $server)
	{
		\App\Log::info('Swoole server starting', 'WebSocket');
	}

	/**
	 * Swoole server shutdown function.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 *
	 * @return void
	 */
	public function onShutdown(Server $server)
	{
		\App\Log::info('Swoole server shutdown', 'WebSocket');
	}

	/**
	 * Swoole start worker function.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 * @param int                      $workerId
	 *
	 * @return void
	 */
	public function onWorkerStart(Server $server, int $workerId)
	{
		\App\Log::info("Swoole worker #$workerId starting", 'WebSocket');
	}

	/**
	 * Swoole stop worker function.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 * @param int                      $workerId
	 *
	 * @return void
	 */
	public function onWorkerStop(Server $server, int $workerId)
	{
		\App\Log::info("Swoole worker #$workerId stopping", 'WebSocket');
	}

	/**
	 * Swoole error worker function.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 * @param int                      $workerId
	 * @param mixed                    $workerPid
	 * @param mixed                    $exitCode
	 * @param mixed                    $signalNo
	 *
	 * @return void
	 */
	public function onWorkerError(Server $server, int $workerId, $workerPid, $exitCode, $signalNo)
	{
		\App\Log::error("Swoole worker error [workerId=$workerId, workerPid=$workerPid, exitCode=$exitCode, signalNo=$signalNo]... | " . swoole_last_error(), 'WebSocket');
	}

	/**
	 *  Swoole request function.
	 *
	 * @param \Swoole\Http\Request  $request
	 * @param \Swoole\Http\Response $response
	 *
	 * @return void
	 */
	public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
	{
		$response->status(405);
		$response->end('<h1>Swoole websocket server is working properly :)</h1>');
	}

	/**
	 * Get container class.
	 *
	 * @return string
	 */
	private function getContainerClass(int $fd): string
	{
		$container = $this->container ?? $this->connections[$fd]['container'];
		return 'App\\Controller\\WebSocket\\' . $container;
	}

	/**
	 * Get container instance.
	 *
	 * @param int $fd
	 *
	 * @return App\Controller\WebSocket\Base
	 */
	public function getContainer(int $fd): WebSocket\Base
	{
		$class = $this->getContainerClass($fd);
		return new $class($this);
	}

	/**
	 * Get connections list.
	 *
	 * @param string $type
	 * @param mixed  $search
	 *
	 * @return array
	 */
	public function getConnections($type = null, $search = null): array
	{
		$return = $connections = [];
		foreach ($this->connections as $key => $value) {
			$connections[$key] = $value;
		}
		if ($type && $search) {
			$column = array_column($connections, $type, 'fd');
			while (false !== ($key = array_search($search, $column))) {
				$return[$key] = $connections[$key];
				unset($column[$key]);
			}
			return $return;
		}
		return $connections;
	}
}

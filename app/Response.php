<?php

/**
 * Response file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Response class.
 */
class Response
{
	/**
	 * Error data variable.
	 *
	 * @var array
	 */
	private $error = [];
	/**
	 * Exception data variable.
	 *
	 * @var array
	 */
	private $exception = [];
	/**
	 * Result variable.
	 *
	 * @var array
	 */
	private $result = [];

	/**
	 * Environment data.
	 */
	private $env;
	/**
	 * Swoole websocket server instance.
	 *
	 * @see https://github.com/swoole/swoole-src
	 *
	 * @var null|\Swoole\WebSocket\Server
	 */
	private $webSocketServer;
	/**
	 * Swoole websocket client ID.
	 *
	 * @var int
	 */
	private $webSocketClientId;

	/**
	 * Set web socket server instance.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 * @param int                      $webSocketFd
	 *
	 * @return void
	 */
	public function setWebSocketServer(\Swoole\WebSocket\Server $server, int $fd)
	{
		$this->webSocketServer = $server;
		$this->webSocketClientId = $fd;
	}

	/**
	 * Set error data to send.
	 *
	 * @param string $message
	 * @param mixed  $moduleName
	 *
	 * @return void
	 */
	public function setError(string $message, $moduleName = 'Other.Exceptions')
	{
		if (false === strpos($message, '||')) {
			$message = Language::translateSingleMod($message, $moduleName);
		} else {
			$params = explode('||', $message);
			$message = call_user_func_array('vsprintf', [Language::translateSingleMod(array_shift($params), $moduleName), $params]);
		}
		$this->error = [
			'message' => $message
		];
		if ($this->isWebSocket()) {
			$this->webSocketServer->server->push($this->webSocketClientId, Json::encode($this->prepare()));
		}
	}

	/**
	 * Set error data exception to send.
	 *
	 * @param \Throwable $e
	 *
	 * @return void
	 */
	public function setException(\Throwable $e)
	{
		$this->exception = ErrorHandler::parseException($e);
	}

	/**
	 * Set the result data.
	 *
	 * @param mixed $result
	 */
	public function set($result)
	{
		$this->result = $result;
	}

	public function setForAll($result)
	{
		if ($this->isWebSocket()) {
			$this->webSocketServer->server->push($this->webSocketClientId, Json::encode($this->prepare()));
		}
	}

	/**
	 * Is there a connection to web socket server function.
	 *
	 * @return bool
	 */
	public function isWebSocket()
	{
		return isset($this->webSocketServer);
	}

	/**
	 * Set environment data.
	 *
	 * @param array $env
	 */
	public function setEnv(array $env)
	{
		$this->env = $env;
	}

	/**
	 * Prepare the response wrapper function.
	 *
	 * @return array
	 */
	private function prepare(): array
	{
		$response = ['success' => true];
		if ($this->exception) {
			$response['success'] = false;
			$response['exception'] = $this->exception;
		} elseif ($this->error) {
			$response['error'] = $this->error;
		} else {
			$response['result'] = $this->result;
		}
		if (!empty($this->env)) {
			$response['env'] = $this->env;
		}
		return $response;
	}

	/**
	 * Send response to client function.
	 *
	 * @return void
	 */
	public function emit(): void
	{
		$response = Json::encode($this->prepare());
		if ($this->isWebSocket()) {
			$this->webSocketServer->server->push($this->webSocketClientId, $response);
		} else {
			$charset = Config::main('default_charset', 'UTF-8');
			header("content-type: text/json; charset={$charset}");
			if ($this->exception) {
				http_response_code($this->exception['code']);
			}
			echo $response;
		}
	}
}

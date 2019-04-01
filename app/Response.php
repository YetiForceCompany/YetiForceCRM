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
	 * Error data.
	 */
	private $error;

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
	 * Set web socket server instance.
	 *
	 * @param \Swoole\WebSocket\Server $server
	 *
	 * @return void
	 */
	public function setWebSocketServer(\Swoole\WebSocket\Server $server)
	{
		$this->webSocketServer = $server;
	}

	/**
	 * Set error data to send.
	 *
	 * @param \Throwable $e
	 *
	 * @return void
	 */
	public function setError(\Throwable $e)
	{
		$this->error = ['code' => $e->getCode(), 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()];
	}

	/**
	 * Set the result data.
	 *
	 * @param mixed $result
	 */
	public function setResult($result)
	{
		$this->result = $result;
	}

	/**
	 * Set key result data.
	 *
	 * @param string $key
	 * @param mixed  $data
	 *
	 * @return void
	 */
	public function set(string $key, $data)
	{
		$this->result[$key] = $data;
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
	 * Prepare the response wrapper.
	 */
	private function getResponse()
	{
		$response = [];
		if (null !== $this->error) {
			$response['success'] = false;
			$response['error'] = $this->error;
		} else {
			$response['success'] = true;
			$response['result'] = $this->result;
		}
		if (null !== $this->env) {
			$response['env'] = $this->env;
		}
		return $response;
	}

	/**
	 * Send response to client.
	 */
	public function emit()
	{
		$charset = Config::main('default_charset', 'UTF-8');
		header("content-type: text/json; charset={$charset}");
		echo \App\Json::encode($this->getResponse());
	}
}

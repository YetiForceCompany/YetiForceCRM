<?php
/**
 * WebSocket file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

use Swoole\Client;

/**
 * WebSocket class.
 */
class WebSocket
{
	/**
	 * @var \App\WebSocket[] Table of connections with WebSocket
	 */
	private static $cache = [];
	/**
	 * Swoole WebSocket client instance variable.
	 *
	 * @var \Swoole\Client\WebSocket
	 */
	private $client;

	/**
	 * Constructor.
	 *
	 * @param array $rawValues
	 * @param bool  $overwrite
	 * @param mixed $path
	 */
	public function __construct(string $host, int $port = 9000, $path = '/')
	{
		$this->client = new Client\WebSocket($host, $port, $path);
		if (!$this->client->connect()) {
			throw new \App\Exceptions\IntegrationException('ERR_CONNECT_TO_SERVER_FAILED');
		}
	}

	/**
	 * Creates the WebSocket connection instance function.
	 *
	 * @param string $path Name of websocket container
	 *
	 * @return \App\WebSocket
	 */
	public static function getInstance(string $path)
	{
		if (isset(static::$cache[$path])) {
			return static::$cache[$path];
		}
		$host = \Config\WebSocket::$host;
		if ('0.0.0.0' === $host) {
			if (\function_exists('swoole_get_local_ip')) {
				$host = current(\swoole_get_local_ip());
			} else {
				$host = $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'];
			}
		}
		return self::$cache[$path] = new self($host, \Config\WebSocket::$port, '/' . $path);
	}

	/**
	 * Send data function.
	 *
	 * @param string $data
	 * @param bool   $recv
	 *
	 * @return mixed
	 */
	public function send(string $data, bool $recv = false)
	{
		$send = $this->client->send($data);
		if ($recv) {
			return $this->client->recv();
		}
		return $send;
	}

	/**
	 * Connection test function.
	 *
	 * @return bool
	 */
	public static function connectionTest(): bool
	{
		$response = false;
		try {
			$response = self::getInstance('System')->send(Json::encode([
				'application_unique_key' => \Config\Main::$application_unique_key,
				'action' => 'test'
			]), true);
		} catch (\Throwable $e) {
			\var_dump($e);
			return false;
		}

		return 'test' === $response;
	}
}

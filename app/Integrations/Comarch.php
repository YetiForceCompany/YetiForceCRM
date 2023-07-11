<?php
/**
 * Main file to integration with Comarch.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations;

use App\Exceptions\AppException;

/**
 * Main class to integration with Comarch.
 */
class Comarch
{
	/** @var string Servers table name */
	public const TABLE_NAME = 'i_#__comarch_servers';
	/** @var string Basic table name */
	public const LOG_TABLE_NAME = 'l_#__comarch';
	/** @var string Config table name */
	public const CONFIG_TABLE_NAME = 'i_#__comarch_config';
	/** @var string Map class table name */
	public const MAP_TABLE_NAME = 'i_#__comarch_map_class';
	/** @var string Config table name */
	public const QUEUE_TABLE_NAME = 'i_#__comarch_queue';

	/** @var callable|null Bath iteration callback */
	public $bathCallback;
	/** @var \App\Integrations\Comarch\Config Config. */
	public $config;
	/** @var \App\Integrations\Comarch\Connector\Base Connector with Comarch. */
	public $connector;
	/** @var \App\Integrations\Comarch\Synchronizer\Base[] Synchronizers instance */
	public $synchronizer = [];

	/**
	 * Constructor. Connect with Comarch and authorize.
	 *
	 * @param int           $serverId
	 * @param callable|null $bathCallback
	 */
	public function __construct(int $serverId, ?callable $bathCallback = null)
	{
		$this->bathCallback = $bathCallback;
		$this->config = Comarch\Config::getInstance($serverId);
		$className = '\\App\\Integrations\\Comarch\\Connector\\' . $this->config->get('connector');
		if (!class_exists($className)) {
			throw new AppException('ERR_CLASS_NOT_FOUND');
		}
		$this->connector = new $className($this->config);
		if (!$this->connector instanceof Comarch\Connector\Base) {
			throw new AppException('ERR_CLASS_MUST_BE||\App\Integrations\Comarch\Connector\Base');
		}
		try {
			$this->connector->authorize();
		} catch (\Throwable $ex) {
			$this->log('Error during authorize', null, $ex);
			\App\Log::error("Error during authorize: \n{$ex->__toString()}", 'Comarch');
		}
	}

	/**
	 * Get connector.
	 *
	 * @return \App\Integrations\Comarch\Connector\Base
	 */
	public function getConnector(): Comarch\Connector\Base
	{
		return $this->connector;
	}

	/**
	 * Get synchronizer object instance.
	 *
	 * @param string $name
	 *
	 * @return \App\Integrations\Comarch\Synchronizer
	 */
	public function getSync(string $name): Comarch\Synchronizer
	{
		if (isset($this->synchronizer[$name])) {
			return $this->synchronizer[$name];
		}
		$className = 'App\\Integrations\\Comarch\\' . $this->config->get('connector') . "\\Synchronizer\\{$name}";
		return $this->synchronizer[$name] = new $className($this);
	}

	/**
	 * Get information about Comarch ERP.
	 *
	 * @return array
	 */
	public function getInfo(): array
	{
		try {
			return $this->getConnector()->getInfo();
		} catch (\Throwable $th) {
			$this->log('Get connection info', null, $th);
			return ['info' => $th->getMessage(), 'count' => []];
		}
		return $this->getConnector()->getInfo();
	}

	/**
	 * Test connection.
	 *
	 * @return string
	 */
	public function testConnection(): string
	{
		$status = '';
		try {
			if (!$this->getConnector()->isAuthorized()) {
				throw new AppException('No authorization');
			}
		} catch (\Throwable $th) {
			$this->log('Test connection error', null, $th);
			$status = '[TestConnection]: ' . $th->getMessage();
		}
		return $status;
	}

	/**
	 * Add log to YetiForce system.
	 *
	 * @param string      $category
	 * @param array       $params
	 * @param ?\Throwable $ex
	 * @param bool        $error
	 *
	 * @return void
	 */
	public function log(string $category, ?array $params, ?\Throwable $ex = null, bool $error = false): void
	{
		if ($ex) {
			$params ??= [];
			$message = $ex->getMessage();
			array_unshift($params, $category);
		} else {
			$message = $category;
		}
		$params = print_r($params, true);
		if ($ex && ($raw = \App\RequestHttp::getRawException($ex))) {
			$params .= PHP_EOL . $raw;
		}
		\App\DB::getInstance('log')->createCommand()
			->insert(self::LOG_TABLE_NAME, [
				'server_id' => $this->config->get('id'),
				'time' => date('Y-m-d H:i:s'),
				'error' => $ex ? 1 : ((int) $error),
				'message' => \App\TextUtils::textTruncate($message, 255),
				'params' => $params ? \App\TextUtils::textTruncate($params, 65535) : null,
				'trace' => $ex ? \App\TextUtils::textTruncate(
					rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $ex->__toString()), PHP_EOL),
					65535
				) : null,
			])->execute();
	}
}

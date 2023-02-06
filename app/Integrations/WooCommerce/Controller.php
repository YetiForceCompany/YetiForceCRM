<?php
/**
 * Main file to integration with WooCommerce.
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

namespace App\Integrations\WooCommerce;

use App\Exceptions\AppException;

/**
 * Main class to integration with WooCommerce.
 */
class Controller
{
	/** @var \App\CronHandler Cron instance */
	public $cron;
	/** @var \App\Integrations\WooCommerce\Config Config. */
	public $config;
	/** @var \App\Integrations\WooCommerce\Connector\Base Connector with WooCommerce. */
	public $connector;
	/** @var \App\Integrations\WooCommerce\Synchronizer\Base[] Synchronizers instance */
	public $synchronizer = [];

	/**
	 * Get connector.
	 *
	 * @return \App\Integrations\WooCommerce\Connector\Base
	 */
	public function getConnector(): Connector\Base
	{
		if (empty($this->connector)) {
			$className = '\\App\\Integrations\\WooCommerce\\Connector\\' . $this->config->get('connector') ?? 'HttpAuth';
			if (!class_exists($className)) {
				throw new \App\Exceptions\AppException('ERR_CLASS_NOT_FOUND');
			}
			$this->connector = new $className($this->config);
			if (!$this->connector instanceof \App\Integrations\WooCommerce\Connector\Base) {
				throw new AppException('ERR_CLASS_MUST_BE||\App\Integrations\WooCommerce\Connector\Base');
			}
		}
		return $this->connector;
	}

	/**
	 * Constructor. Connect with WooCommerce and authorize.
	 *
	 * @param int              $serverId
	 * @param \App\CronHandler $cron
	 */
	public function __construct(int $serverId, \App\CronHandler $cron)
	{
		$this->cron = $cron;
		$this->config = \App\Integrations\WooCommerce\Config::getInstance($serverId);
	}

	/**
	 * Get synchronizer object instance.
	 *
	 * @param string $name
	 *
	 * @return Synchronizer\Base
	 */
	public function getSync(string $name): Synchronizer\Base
	{
		if (isset($this->synchronizer[$name])) {
			return $this->synchronizer[$name];
		}
		$className = "App\\Integrations\\WooCommerce\\Synchronizer\\{$name}";
		return $this->synchronizer[$name] = new $className($this);
	}
}

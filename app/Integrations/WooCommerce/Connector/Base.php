<?php
/**
 * Base file which must have each connector.
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

namespace App\Integrations\WooCommerce\Connector;

/**
 * Base class which must have each connector.
 */
abstract class Base
{
	/** @var \App\Integrations\WooCommerce\Config Config instance. */
	public $config;

	/**
	 * Constructor.
	 *
	 * @param \App\Integrations\WooCommerce\Config $config
	 */
	public function __construct(\App\Integrations\WooCommerce\Config $config)
	{
		$this->config = $config;
	}

	/**
	 * Function to send request.
	 *
	 * @param string $method
	 * @param string $action
	 * @param array  $params
	 *
	 * @return string
	 */
	abstract public function request(string $method, string $action, array $params = []): string;
}

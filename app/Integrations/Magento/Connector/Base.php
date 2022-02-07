<?php
/**
 * Base for connectors.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Connector;

/**
 * Base which must have each connector.
 */
abstract class Base
{
	/**
	 * Config.
	 *
	 * @var \App\Integrations\Magento\Config
	 */
	public $config;

	/**
	 * Constructor.
	 *
	 * @param \App\Integrations\Magento\Config $config
	 */
	public function __construct(\App\Integrations\Magento\Config $config)
	{
		$this->config = $config;
	}

	/**
	 * Authorization.
	 *
	 * @return void
	 */
	abstract public function authorize();

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

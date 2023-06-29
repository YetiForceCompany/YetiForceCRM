<?php
/**
 * Base file for the connector enabling connection with a specific version of Comarch.
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

namespace App\Integrations\Comarch\Connector;

/**
 * Base class for the connector enabling connection with a specific version of Comarch.
 */
abstract class Base
{
	/** @var string Connector name */
	const NAME = 'Comarch';
	/** @var string Log name */
	const LOG_NAME = 'App\Integrations\Comarch';
	/** @var \App\Integrations\Comarch\Config Config instance. */
	public $config;

	/**
	 * Constructor.
	 *
	 * @param \App\Integrations\Comarch\Config $config
	 */
	public function __construct(\App\Integrations\Comarch\Config $config)
	{
		$this->config = $config;
	}

	/**
	 * Authorization.
	 *
	 * @return void
	 */
	abstract public function authorize(): void;

	/**
	 * Check if authorized.
	 *
	 * @return bool
	 */
	abstract public function isAuthorized(): bool;

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

	/**
	 * Get information about Comarch ERP.
	 *
	 * @return array
	 */
	abstract public function getInfo(): array;
}

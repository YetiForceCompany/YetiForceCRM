<?php
/**
 * Interface for connectors.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Integrations\Magento\Connector;

/**
 * Interface which must have each connector.
 */
interface ConnectorInterface
{
	/**
	 * Authorization.
	 *
	 * @return void
	 */
	public function authorize();

	/**
	 * Function to send request.
	 *
	 * @param string $method
	 * @param string $action
	 * @param array  $params
	 *
	 * @return string
	 */
	public function request(string $method, string $action, array $params = []): string;
}

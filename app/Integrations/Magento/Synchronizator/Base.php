<?php
/**
 * Synchronize.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator;

/**
 * Base class to synchronization.
 */
abstract class Base
{
	/**
	 * Connector.
	 *
	 * @var object
	 */
	protected $connector;

	/**
	 * Sets connector to communicate with system.
	 *
	 * @param object $connector
	 *
	 * @return void
	 */
	public function setConnector($connector)
	{
		$this->connector = $connector;
	}

	/**
	 * Main function.
	 *
	 * @return void
	 */
	abstract public function process();
}

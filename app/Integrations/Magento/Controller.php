<?php
/**
 * Main class to integration with magento.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Integrations\Magento;

use App\Exceptions\AppException;
use App\Integrations\Magento\Connector\ConnectorInterface;

/**
 * Magento class.
 */
class Controller
{
	/**
	 * Connector with magento.
	 */
	public $connector;

	/**
	 * Returns connector.
	 *
	 * @return object
	 */
	public function getConnector()
	{
		if (empty($this->connector)) {
			$className = '\\App\\Integrations\\Magento\\Connector\\' . \App\Config::component('Magento', 'connector', 'Token');
			if (!class_exists($className)) {
				throw new \App\Exceptions\AppException('ERR_CLASS_NOT_FOUND');
			}
			$this->connector = new $className();
			if (!$this->connector instanceof ConnectorInterface) {
				throw new AppException('ERR_CLASS_MUST_HAVE_INTERFACE||ConnectorInterface');
			}
		}
		return $this->connector;
	}

	/**
	 * Constructor. Connect with magento and authorize.
	 */
	public function __construct()
	{
		$this->getConnector()->authorize();
	}

	/**
	 * Synchronize categories for products.
	 *
	 * @return void
	 */
	public function synchronizeCategories()
	{
		$categorySynchronizator = new Synchronizator\Category();
		$categorySynchronizator->setConnector($this->getConnector());
		$categorySynchronizator->process();
	}

	/**
	 * Synchronize products.
	 *
	 * @return void
	 */
	public function synchronizeProducts()
	{
		$categorySynchronizator = new Synchronizator\Product();
		$categorySynchronizator->setConnector($this->getConnector());
		$categorySynchronizator->process();
	}
}

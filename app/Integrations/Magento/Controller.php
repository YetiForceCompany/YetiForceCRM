<?php
/**
 * Main class to integration with magento.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento;

use App\Exceptions\AppException;

/**
 * Magento class.
 */
class Controller
{
	/**
	 * Config.
	 *
	 * @var \App\Integrations\Magento\Config
	 */
	public $config;
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
			$className = '\\App\\Integrations\\Magento\\Connector\\' . $this->config->get('connector') ?? 'Token';
			if (!class_exists($className)) {
				throw new \App\Exceptions\AppException('ERR_CLASS_NOT_FOUND');
			}
			$this->connector = new $className($this->config);
			if (!$this->connector instanceof \App\Integrations\Magento\Connector\Base) {
				throw new AppException('ERR_CLASS_MUST_BE||\App\Integrations\Magento\Connector\Base');
			}
		}
		return $this->connector;
	}

	/**
	 * Constructor. Connect with magento and authorize.
	 *
	 * @param int $serverId
	 */
	public function __construct(int $serverId)
	{
		$this->config = \App\Integrations\Magento\Config::getInstance($serverId);
		$this->getConnector()->authorize();
	}

	/**
	 * Synchronize categories for products.
	 *
	 * @return void
	 */
	public function synchronizeCategories(): void
	{
		$categorySynchronizer = new Synchronizer\Category($this);
		$categorySynchronizer->process();
	}

	/**
	 * Synchronize currencies for products.
	 *
	 * @return void
	 */
	public function synchronizeCurrencies(): void
	{
		$currencySynchronizer = new Synchronizer\Currency($this);
		$currencySynchronizer->process();
	}

	/**
	 * Synchronize products.
	 *
	 * @return void
	 */
	public function synchronizeProducts(): void
	{
		$productSynchronizer = new Synchronizer\Product($this);
		$productSynchronizer->process();
	}

	/**
	 * Synchronize products.
	 *
	 * @throws AppException
	 */
	public function synchronizeInvoices(): void
	{
		$invoiceSynchronizer = new Synchronizer\Invoice($this);
		$invoiceSynchronizer->process();
	}

	/**
	 * Synchronize orders.
	 *
	 * @throws AppException
	 */
	public function synchronizeOrders(): void
	{
		$orderSynchronizer = new Synchronizer\Order($this);
		$orderSynchronizer->process();
	}

	/**
	 * Synchronize orders.
	 *
	 * @throws AppException
	 */
	public function synchronizeCustomers(): void
	{
		$customerSynchronizer = new Synchronizer\Customer($this);
		$customerSynchronizer->process();
	}

	/**
	 * Update inventory stock in all magento.
	 *
	 * @param int $storageId
	 * @param int $product
	 *
	 * @return void
	 */
	public static function updateStock(int $storageId, int $product): void
	{
		foreach (Config::getAllServers() as $serverId => $config) {
			if (0 === (int) $config['status'] || 'None' === $config['storage_quantity_location']) {
				continue;
			}
			$customerSynchronizer = new Synchronizer\InventoryStock(new self($serverId));
			$customerSynchronizer->storageId = $storageId;
			$customerSynchronizer->product = $product;
			$customerSynchronizer->process();
		}
	}
}

<?php
/**
 * Synchronize.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer;

/**
 * Base class to synchronization.
 */
abstract class Base
{
	/**
	 * Connector.
	 *
	 * @var \App\Integrations\Magento\Connector\Token
	 */
	protected $connector;
	/**
	 * Last scan config data.
	 *
	 * @var array
	 */
	public $lastScan = [];
	/**
	 * Config.
	 *
	 * @var \App\Integrations\Magento\Config
	 */
	public $config;
	/**
	 * Controller.
	 *
	 * @var \App\Integrations\Magento\Controller
	 */
	public $controller;

	/**
	 * Constructor.
	 *
	 * @param \App\Integrations\Magento\Controller $controller
	 */
	public function __construct(\App\Integrations\Magento\Controller $controller)
	{
		$this->connector = $controller->getConnector();
		$this->controller = $controller;
		$this->config = $controller->config;
	}

	/**
	 * Main function.
	 *
	 * @return void
	 */
	abstract public function process();

	/**
	 * Return parsed time to magento time zone.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function getFormattedTime(string $value): string
	{
		return \DateTimeField::convertTimeZone($value, \App\Fields\DateTime::getTimeZone(), 'UTC')->format('Y-m-d H:i:s');
	}

	/**
	 * Save inventory elements.
	 *
	 * @param \Vtiger_Record_Model                                  $recordModel
	 * @param \App\Integrations\Magento\Synchronizer\Maps\Inventory $mapModel
	 *
	 * @return bool
	 */
	public function saveInventoryCrm(\Vtiger_Record_Model $recordModel, Maps\Inventory $mapModel): bool
	{
		$inventoryData = [];
		$savedAllProducts = true;
		if ($mapModel->dataCrm['currency_id']) {
			foreach ($mapModel->data['items'] as $item) {
				$productId = $this->findProduct(trim($item['sku']));
				if (0 === $productId) {
					$productId = $mapModel->createProduct($item);
				}
				if ($productId) {
					$item['crmProductId'] = $productId;
					$inventoryData[] = $this->parseInventoryData($recordModel, $item, $mapModel);
				} else {
					$savedAllProducts = false;
					\App\Log::error('Skipped saving record, product not found in CRM (magento id: [' . $item['product_id'] . '] | SKU:[' . $item['sku'] . '])', 'Integrations/Magento');
					$this->log('Skipped saving record, product not found in CRM (magento id: [' . $item['product_id'] . '] | SKU:[' . $item['sku'] . '])');
					break;
				}
			}
		} else {
			$savedAllProducts = false;
		}
		if ($savedAllProducts && !empty($inventoryData)) {
			if (!empty($mapModel->data['extension_attributes']['shipping_assignments']) && ($shipping = $this->parseShippingData($mapModel))) {
				$inventoryData[] = $shipping;
			}
			if ($additionalData = $this->findAdditionalData($mapModel)) {
				$inventoryData[] = $additionalData;
			}
			$recordModel->initInventoryData($inventoryData, false);
		}
		return $savedAllProducts;
	}

	/**
	 * Parse inventory data to YetiForce format.
	 *
	 * @param \Vtiger_Record_Model                                  $recordModel
	 * @param array                                                 $item
	 * @param \App\Integrations\Magento\Synchronizer\Maps\Inventory $mapModel
	 *
	 * @return array
	 */
	public function parseInventoryData(\Vtiger_Record_Model $recordModel, array $item, Maps\Inventory $mapModel): array
	{
		$mapModel->setDataInv($item);
		$inventoryModel = \Vtiger_Inventory_Model::getInstance($recordModel->getModuleName());
		$inventoryRow = $inventoryModel->loadRowData($item['crmProductId'], ['currency' => $mapModel->dataCrm['currency_id']]);
		foreach ($inventoryModel->getFields() as $columnName => $fieldModel) {
			if (\in_array($fieldModel->getColumnName(), ['name', 'total', 'margin', 'marginp', 'net', 'gross'])) {
				continue;
			}
			if ('tax_percent' === $columnName || 'tax' === $columnName) {
				$tax = $item['tax_percent'] ?? round($item['tax_amount'] / $item['row_total'] * 100);
				$inventoryRow['taxparam'] = '{"aggregationType":"individual","individualTax":' . $tax . '}';
			} elseif ('taxmode' === $columnName) {
				$inventoryRow['taxmode'] = 1;
			} elseif ('discountmode' === $columnName) {
				$inventoryRow['discountmode'] = 1;
			} elseif ('discount' === $columnName) {
				if (empty($item['discount_amount']) && !empty($item['discount_percent'])) {
					$inventoryRow['discountparam'] = '{"aggregationType":"individual","individualDiscountType":"percentage","individualDiscount":' . $item['discount_percent'] . '}';
				} else {
					$inventoryRow['discountparam'] = '{"aggregationType":"individual","individualDiscountType":"amount","individualDiscount":' . $mapModel->getInvFieldValue('discount') . '}';
				}
			} elseif ('currency' === $columnName) {
				$inventoryRow['currency'] = $mapModel->dataCrm['currency_id'];
			} else {
				$value = $mapModel->getInvFieldValue($columnName);
				if (null !== $value) {
					$inventoryRow[$columnName] = $value;
				} elseif (!isset($inventoryRow[$columnName])) {
					$inventoryRow[$columnName] = $fieldModel->getDefaultValue();
				}
			}
		}
		return $inventoryRow;
	}

	/**
	 * Parse shipping data.
	 *
	 * @param \App\Integrations\Magento\Synchronizer\Maps\Inventory $mapModel
	 *
	 * @return array
	 */
	public function parseShippingData(Maps\Inventory $mapModel): array
	{
		$data = current($mapModel->data['extension_attributes']['shipping_assignments']);
		if ($this->config->get('shipping_service_id') && !empty($data['shipping']['total'])) {
			$tax = $data['shipping']['total']['shipping_amount'] > 0 ? ($data['shipping']['total']['shipping_tax_amount'] / $data['shipping']['total']['shipping_amount'] * 100) : 0;
			return [
				'discountmode' => 1,
				'taxmode' => 1,
				'currency' => $mapModel->dataCrm['currency_id'],
				'name' => $this->config->get('shipping_service_id'),
				'unit' => '',
				'subunit' => '',
				'qty' => 1,
				'price' => $data['shipping']['total']['shipping_amount'],
				'discountparam' => '{"aggregationType":"individual","individualDiscountType":"amount","individualDiscount":' . $data['shipping']['total']['shipping_discount_amount'] . '}',
				'purchase' => 0,
				'taxparam' => '{"aggregationType":"individual","individualTax":' . round($tax) . '}',
				'comment1' => '',
			];
		}
		return [];
	}

	/**
	 * Parse additional data.
	 *
	 * @param \App\Integrations\Magento\Synchronizer\Maps\Inventory $mapModel
	 *
	 * @return array
	 */
	public function findAdditionalData(Maps\Inventory $mapModel): array
	{
		$additionalData = [];
		if (method_exists($mapModel, 'addAdditionalInvData')) {
			$additionalData = $mapModel->addAdditionalInvData();
		}
		return $additionalData;
	}

	/**
	 * Find product id by ean.
	 *
	 * @param string $ean
	 *
	 * @return int
	 */
	public function findProduct(string $ean): int
	{
		if (\App\Cache::staticHas('ProductIdByEan', $ean)) {
			return \App\Cache::staticGet('ProductIdByEan', $ean);
		}
		$id = (new \App\Db\Query())->select(['productid'])->from('vtiger_products')
			->innerJoin('vtiger_crmentity', 'vtiger_products.productid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_products.ean' => $ean])->scalar() ?: 0;
		\App\Cache::staticSave('ProductIdByEan', $ean, $id);
		return $id;
	}

	/**
	 * Add log to db.
	 *
	 * @param string      $category
	 * @param ?\Throwable $ex
	 *
	 * @return void
	 */
	public function log(string $category, ?\Throwable $ex = null): void
	{
		\App\DB::getInstance('log')->createCommand()
			->insert('l_#__magento', [
				'time' => date('Y-m-d H:i:s'),
				'category' => $ex ? $category : 'info',
				'message' => $ex ? $ex->getMessage() : $category,
				'code' => $ex ? $ex->getCode() : 500,
				'trace' => $ex ? $ex->__toString() : null,
			])->execute();
	}
}

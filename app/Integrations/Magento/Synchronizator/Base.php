<?php
/**
 * Synchronize.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator;

use App\Db\Query;

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
	 * Map query instance.
	 *
	 * @var \App\Db\Query
	 */
	public $mapQuery;
	/**
	 * Records map from magento.
	 *
	 * @var array
	 */
	public $map = [];
	/**
	 * Records map from YetiForce.
	 *
	 * @var array
	 */
	public $mapCrm = [];
	/**
	 * Mapped id to sku.
	 *
	 * @var array
	 */
	public $mapIdToSkuCrm = [];
	/**
	 * Mapped sku to id.
	 *
	 * @var array
	 */
	public $mapSkuToIdCrm = [];
	/**
	 * Records map keys from magento.
	 *
	 * @var array
	 */
	public $mapKeys = [];
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
	 * Mapped records table name.
	 *
	 * @var string
	 */
	public const TABLE_NAME = 'i_#__magento_record';
	/**
	 * Magento variable value.
	 *
	 * @var string
	 */
	public const MAGENTO = 1;
	/**
	 * YetiForce variable value.
	 *
	 * @var string
	 */
	public const YETIFORCE = 2;

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
	 * Get record mapping.
	 *
	 * @param string   $type
	 * @param bool|int $fromId
	 * @param bool|int $limit
	 */
	public function getMapping(string $type, $fromId = false, $limit = false): void
	{
		$this->mapQuery = (new Query())
			->select(['crmid', 'id'])
			->where(['type' => $type]);
		if (false !== $fromId) {
			$this->mapQuery = $this->mapQuery->andWhere(['>', 'id', $fromId]);
		}
		if (false !== $limit) {
			$this->mapQuery = $this->mapQuery->limit($limit);
		}
		$this->map[$type] = $this->mapQuery->from(self::TABLE_NAME)
			->orderBy(['id' => SORT_ASC])
			->createCommand()->queryAllByGroup(0) ?? [];
		$this->mapCrm[$type] = \array_flip($this->map[$type]);
		$this->mapKeys[$type] = \array_keys($this->map[$type]);
	}

	/**
	 * Update record mapping.
	 *
	 * @param int $recordId
	 * @param int $recordIdCrm
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function updateMapping(int $recordId, int $recordIdCrm): int
	{
		return \App\Db::getInstance()->createCommand()->update(self::TABLE_NAME, [
			'id' => $recordId
		], ['crmid' => $recordIdCrm])->execute();
	}

	/**
	 * Save record mapping.
	 *
	 * @param int    $recordId
	 * @param int    $recordIdCrm
	 * @param string $type
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function saveMapping(int $recordId, int $recordIdCrm, string $type): int
	{
		if (isset($this->mapCrm[$type][$recordId]) || isset($this->map[$type][$recordIdCrm])) {
			$result = $this->updateMapping($recordId, $recordIdCrm);
		} else {
			$result = \App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, [
				'id' => $recordId,
				'crmid' => $recordIdCrm,
				'type' => $type
			])->execute();
		}
		$this->map[$type][$recordIdCrm] = $recordId;
		$this->mapCrm[$type][$recordId] = $recordIdCrm;
		return $result;
	}

	/**
	 * Method to delete mapping.
	 *
	 * @param int    $recordId
	 * @param int    $recordIdCrm
	 * @param string $type
	 *
	 * @throws \yii\db\Exception
	 */
	public function deleteMapping(int $recordId, int $recordIdCrm, string $type): void
	{
		\App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, ['crmid' => $recordIdCrm])->execute();
		unset($this->map[$type][$recordIdCrm], $this->mapCrm[$type][$recordId]);
	}

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
	 * @param \Vtiger_Record_Model                                    $recordModel
	 * @param \App\Integrations\Magento\Synchronizator\Maps\Inventory $mapModel
	 *
	 * @return bool
	 */
	public function saveInventoryCrm(\Vtiger_Record_Model $recordModel, Maps\Inventory $mapModel): bool
	{
		$inventoryData = [];
		$savedAllProducts = true;
		foreach ($mapModel->data['items'] as $record) {
			$productId = $this->findProduct($record['sku']);
			if (0 === $productId) {
				$productId = $mapModel->createProduct($record);
			}
			if ($productId) {
				$record['crmProductId'] = $productId;
				$inventoryData[] = $this->parseInventoryData($recordModel, $record, $mapModel);
			} else {
				$savedAllProducts = false;
				\App\Log::error('Skipped saving record, product not found in CRM (magento id: [' . $record['product_id'] . '] | SKU:[' . $record['sku'] . '])', 'Integrations/Magento');
			}
		}
		if ($savedAllProducts && !empty($inventoryData)) {
			if (!empty($mapModel->data['extension_attributes']['shipping_assignments']) && ($shipping = $this->parseShippingData($mapModel->data['extension_attributes']['shipping_assignments']))) {
				$inventoryData[] = $shipping;
			}
			if ($additionalData = $this->findAdditionalData($mapModel->data)) {
				$inventoryData[] = $additionalData;
			}
			$recordModel->initInventoryData($inventoryData, false);
		}
		return $savedAllProducts;
	}

	/**
	 * Parse inventory data to YetiForce format.
	 *
	 * @param \Vtiger_Record_Model                                    $recordModel
	 * @param array                                                   $record
	 * @param \App\Integrations\Magento\Synchronizator\Maps\Inventory $mapModel
	 *
	 * @return array
	 */
	public function parseInventoryData(\Vtiger_Record_Model $recordModel, array $record, Maps\Inventory $mapModel): array
	{
		$mapModel->setData($record);
		$item = [];
		foreach (\Vtiger_Inventory_Model::getInstance($recordModel->getModuleName())->getFields() as $columnName => $fieldModel) {
			if (\in_array($fieldModel->getColumnName(), ['total', 'margin', 'marginp', 'net', 'gross'])) {
				continue;
			}
			if ('tax_percent' === $columnName || 'tax' === $columnName) {
				$item['taxparam'] = '{"aggregationType":"individual","individualTax":' . $record['tax_percent'] . '}';
			} elseif ('taxmode' === $columnName) {
				$item['taxmode'] = 1;
			} elseif ('discountmode' === $columnName) {
				$item['discountmode'] = 1;
			} elseif ('discount' === $columnName) {
				if (empty($record['discount_amount']) && !empty($record['discount_percent'])) {
					$item['discountparam'] = '{"aggregationType":"individual","individualDiscountType":"percentage","individualDiscount":' . $record['discount_percent'] . '}';
				} else {
					$item['discountparam'] = '{"aggregationType":"individual","individualDiscountType":"amount","individualDiscount":' . $mapModel->getInvFieldValue('discount') . '}';
				}
			} elseif ('currency' === $columnName) {
				$item['currency'] = $this->config->get('currencyId');
			} elseif ('name' === $columnName) {
				$item[$columnName] = $record['crmProductId'];
			} else {
				$item[$columnName] = $mapModel->getInvFieldValue($columnName) ?? $fieldModel->getDefaultValue();
			}
		}
		return $item;
	}

	/**
	 * Parse shipping data.
	 *
	 * @param array $shippingData
	 *
	 * @return array
	 */
	public function parseShippingData(array $shippingData): array
	{
		$data = current($shippingData);
		if ($this->config->get('shipping_service_id') && !empty($data['shipping']['total'])) {
			return [
				'discountmode' => 1,
				'taxmode' => 1,
				'currency' => $this->config->get('currencyId'),
				'name' => $this->config->get('shipping_service_id'),
				'unit' => '',
				'subunit' => '',
				'qty' => 1,
				'price' => $data['shipping']['total']['shipping_amount'],
				'discountparam' => '{"aggregationType":"individual","individualDiscountType":"amount","individualDiscount":' . $data['shipping']['total']['shipping_discount_amount'] . '}',
				'purchase' => 0,
				'taxparam' => '{"aggregationType":"individual","individualTax":' . $data['shipping']['total']['shipping_tax_amount'] . '}',
				'comment1' => ''
			];
		}
		return [];
	}

	/**
	 * Parse additional data.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function findAdditionalData(array $data = []): array
	{
		$additionalData = [];
		if (method_exists($this, 'addAdditionalInvData')) {
			$additionalData = $this->addAdditionalInvData($data);
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
		if (\App\Cache::staticHas('ProductsByEan', $ean)) {
			return \App\Cache::staticGet('ProductsByEan', $ean);
		}
		$id = (new \App\Db\Query())->select(['productid'])->from('vtiger_products')
			->innerJoin('vtiger_crmentity', 'vtiger_products.productid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_products.ean' => $ean])->scalar() ?: 0;
		\App\Cache::staticSave('ProductsByEan', $ean, $id);
		return $id;
	}
}

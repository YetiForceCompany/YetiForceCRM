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
	 * Records map from yetiforce.
	 *
	 * @var array
	 */
	public $mapCrm = [];
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
	 * Sets connector to communicate with system.
	 *
	 * @param object $connector
	 *
	 * @return void
	 */
	public function setConnector($connector): void
	{
		$this->connector = $connector;
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
	 * @param $value
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
	 * @param $recordModel
	 * @param $records
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public function saveInventoryCrm($recordModel, $records): bool
	{
		$inventoryData = [];
		$savedAllProducts = true;
		foreach ($records as $record) {
			if (isset($this->mapCrm['product'][$record['product_id']])) {
				$inventoryData[] = $this->parseInventoryData($recordModel, $record);
			} else {
				$savedAllProducts = false;
				\App\Log::error('Error during saving order. Inventory product does not exist in YetiForce.', 'Integrations/Magento');
			}
		}
		if (!empty($inventoryData)) {
			$recordModel->initInventoryData($inventoryData, false);
		}
		return $savedAllProducts;
	}

	/**
	 * Parse inventory data to YetiForce format.
	 *
	 * @param $recordModel
	 * @param $record
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function parseInventoryData($recordModel, $record): array
	{
		$orderFields = new \App\Integrations\Magento\Synchronizator\Maps\Order();
		$orderFields->setData($record);
		$item = [];
		foreach (\Vtiger_Inventory_Model::getInstance($recordModel->getModuleName())->getFields() as $columnName => $fieldModel) {
			if (\in_array($fieldModel->getColumnName(), ['total', 'margin', 'marginp', 'net', 'gross'])) {
				continue;
			}
			if ('tax_percent' === $columnName || 'tax' === $columnName) {
				$item['taxparam'] = '{"aggregationType":"individual","individualTax":' . $record['tax_percent'] . '}';
			} elseif ('taxmode' === $columnName) {
				$item['taxmode'] = 1;
			} elseif ('discount' === $columnName) {
				if (empty($record['discount_amount']) && !empty($record['discount_percent'])) {
					$item['discountparam'] = '{"aggregationType":"individual","individualDiscountType":"percentage","individualDiscount":' . $record['discount_percent'] . '}';
				} else {
					$item['discountparam'] = '{"aggregationType":"individual","individualDiscountType":"amount","individualDiscount":' . $record['discount_amount'] . '}';
				}
			} elseif ('currency' === $columnName) {
				$item['currency'] = 1;
			} elseif ('name' === $columnName) {
				$item[$columnName] = $this->mapCrm['product'][$record['product_id']];
			} else {
				$item[$columnName] = $orderFields->getInvFieldValue($columnName) ?? $fieldModel->getDefaultValue();
			}
		}
		return $item;
	}
}

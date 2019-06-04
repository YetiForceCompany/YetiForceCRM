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
	 * @var object
	 */
	protected $connector;
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
	 * Last scan config data.
	 *
	 * @var array
	 */
	public $lastScan = [];
	/**
	 * Config.
	 *
	 * @var object
	 */
	public $config;
	/**
	 * Mapped fields.
	 *
	 * @var array
	 */
	public $mappedFields = [];
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
	 * Yetiforce variable value.
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
		$this->map = (new Query())
			->select(['crmid', 'id'])
			->where(['type' => $type]);
		if (false !== $fromId) {
			$this->map = $this->map->andWhere(['>', 'id', $fromId]);
		}
		if (false !== $limit) {
			$this->map = $this->map->limit($limit);
		}
		$this->map = $this->map->from(self::TABLE_NAME)
			->orderBy(['id' => SORT_ASC])
			->createCommand()->queryAllByGroup(0) ?? [];
		$this->mapCrm = \array_flip($this->map);
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
		if (isset($this->mapCrm[$recordId]) || isset($this->map[$recordIdCrm])) {
			$result = $this->updateMapping($recordId, $recordIdCrm);
		} else {
			$result = \App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, [
				'id' => $recordId,
				'crmid' => $recordIdCrm,
				'type' => $type
			])->execute();
		}
		$this->map[$recordIdCrm] = $recordId;
		$this->mapCrm[$recordId] = $recordIdCrm;
		return $result;
	}

	/**
	 * Method to delete mapping.
	 *
	 * @param int $recordId
	 * @param int $recordIdCrm
	 *
	 * @throws \yii\db\Exception
	 */
	public function deleteMapping(int $recordId, int $recordIdCrm): void
	{
		\App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, ['crmid' => $recordIdCrm])->execute();
		unset($this->map[$recordIdCrm], $this->mapCrm[$recordId]);
	}

	/**
	 * Method to parse data with mapped fields.
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function getData(array $data): array
	{
		$fields = [];
		foreach ($this->mappedFields as $fieldNameCrm => $fieldName) {
			$fields[$fieldNameCrm] = $data[$fieldName];
		}
		return $fields;
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
}

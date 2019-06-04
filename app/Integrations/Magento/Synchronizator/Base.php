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
	public $mapMagento = [];
	/**
	 * Records map from yetiforce.
	 *
	 * @var array
	 */
	public $mapYF = [];
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
		$this->mapMagento = (new Query())
			->select(['crmid', 'id'])
			->where(['type' => $type]);
		if (false !== $fromId) {
			$this->mapMagento = $this->mapMagento->andWhere(['>', 'id', $fromId]);
		}
		if (false !== $limit) {
			$this->mapMagento = $this->mapMagento->limit($limit);
		}
		$this->mapMagento = $this->mapMagento->from('i_#__magento_record')
			->orderBy(['id' => SORT_ASC])
			->createCommand()->queryAllByGroup(0) ?? [];
		$this->mapYF = \array_flip($this->mapMagento);
	}

	/**
	 * Update record mapping.
	 *
	 * @param int $recordIdMagento
	 * @param int $recordIdYF
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function updateMapping(int $recordIdMagento, int $recordIdYF): int
	{
		return \App\Db::getInstance()->createCommand()->update('i_#__magento_record', [
			'id' => $recordIdMagento
		], ['crmid' => $recordIdYF])->execute();
	}

	/**
	 * Save record mapping.
	 *
	 * @param int    $recordIdMagento
	 * @param int    $recordIdYF
	 * @param string $type
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function saveMapping(int $recordIdMagento, int $recordIdYF, string $type): int
	{
		if (isset($this->mapYF[$recordIdMagento]) || isset($this->mapMagento[$recordIdYF])) {
			$result = $this->updateMapping($recordIdMagento, $recordIdYF);
		} else {
			$result = \App\Db::getInstance()->createCommand()->insert('i_#__magento_record', [
				'id' => $recordIdMagento,
				'crmid' => $recordIdYF,
				'type' => $type
			])->execute();
		}
		$this->mapMagento[$recordIdYF] = $recordIdMagento;
		$this->mapYF[$recordIdMagento] = $recordIdYF;
		return $result;
	}

	/**
	 * Method to delete mapping.
	 *
	 * @param int $recordIdMagento
	 * @param int $recordIdYF
	 *
	 * @throws \yii\db\Exception
	 */
	public function deleteMapping(int $recordIdMagento, int $recordIdYF): void
	{
		\App\Db::getInstance()->createCommand()->delete('i_#__magento_record', ['crmid' => $recordIdYF])->execute();
		unset($this->mapMagento[$recordIdYF], $this->mapYF[$recordIdMagento]);
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
		foreach ($this->mappedFields as $fieldNameYF => $fieldNameMagento) {
			$fields[$fieldNameYF] = $data[$fieldNameMagento];
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

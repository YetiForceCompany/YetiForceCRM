<?php
/**
 * Class to read and save configuration for integration with magento.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento;

use App\Db\Query;

/**
 * Class Config.
 */
class Config extends \App\Base
{
	/**
	 * Instance class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Function to get object to read configuration.
	 *
	 * @return config
	 */
	public static function getInstance()
	{
		if (null === static::$instance) {
			static::$instance = new static();
			$data = (new Query())->select(['name', 'value'])->from('i_#__magento_config')->createCommand()->queryAllByGroup();
			static::$instance->setData($data);
		}
		return static::$instance;
	}

	/**
	 * Method to update data after update in db.
	 */
	public static function updateData(): void
	{
		static::$instance->setData((new Query())->select(['name', 'value'])->from('i_#__magento_config')->createCommand()->queryAllByGroup());
	}

	/**
	 * Save in db last scanned id.
	 *
	 * @param string $type
	 * @param string $name
	 * @param int    $id
	 *
	 * @throws \yii\db\Exception
	 */
	public static function setLastScanId(string $type, string $name, int $id): void
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$data = [
			'name' => "{$type}_last_scan_{$name}",
			'value' => $id
		];
		if (!(new Query())->from('i_#__magento_config')
			->where(['name' => $data['name']])
			->exists()) {
			$dbCommand->insert('i_#__magento_config', $data)->execute();
		} else {
			$dbCommand->update('i_#__magento_config', $data, ['name' => $data['name']])->execute();
		}
		static::updateData();
	}

	/**
	 * Set start scan.
	 *
	 * @param string $type
	 *
	 * @throws \yii\db\Exception
	 */
	public static function setStartScan(string $type): void
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$data = [
			'name' => $type . '_start_scan_date',
			'value' => date('Y-m-d H:i:s')
		];
		if (!(new Query())->from('i_#__magento_config')
			->where(['name' => $data['name']])
			->exists()) {
			$dbCommand->insert('i_#__magento_config', $data)->execute();
		} else {
			$dbCommand->update('i_#__magento_config', $data, ['name' => $data['name']])->execute();
		}
		static::updateData();
	}

	/**
	 * Set end scan.
	 *
	 * @param string $type
	 * @param string $date
	 *
	 * @throws \yii\db\Exception
	 */
	public static function setEndScan(string $type, string $date): void
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (!$date) {
			$date = date('Y-m-d H:i:s');
		}
		$saveData = [
			[
				'name' => $type . '_end_scan_date',
				'value' => $date
			], [
				'name' => $type . '_last_scan_id',
				'value' => 0
			], [
				'name' => $type . '_last_scan_idcrm',
				'value' => 0
			], [
				'name' => $type . '_last_scan_idmap',
				'value' => 0
			]
		];
		foreach ($saveData as $data) {
			if (!(new Query())->from('i_#__magento_config')
				->where(['name' => $data['name']])
				->exists()) {
				$dbCommand->insert('i_#__magento_config', $data)->execute();
			} else {
				$dbCommand->update('i_#__magento_config', $data, ['name' => $data['name']])->execute();
			}
		}
		static::updateData();
	}

	/**
	 * Get last scan information.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function getLastScan(string $type): array
	{
		return [
			'id' => self::getInstance()->get($type . '_last_scan_id') ?? 0,
			'idcrm' => self::getInstance()->get($type . '_last_scan_idcrm') ?? 0,
			'idmap' => self::getInstance()->get($type . '_last_scan_idmap') ?? 0,
			'start_date' => self::getInstance()->get($type . '_start_scan_date') ?? false,
			'end_date' => self::getInstance()->get($type . '_end_scan_date') ?? false,
		];
	}
}

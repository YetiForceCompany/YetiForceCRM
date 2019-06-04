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
	private const TABLE_NAME = 'i_#__magento_config';

	/**
	 * Function to get object to read configuration.
	 *
	 * @return object
	 */
	public static function getInstance()
	{
		if (null === static::$instance) {
			static::$instance = new static();
			$data = (new Query())->select(['name', 'value'])->from(self::TABLE_NAME)->createCommand()->queryAllByGroup();
			static::$instance->setData($data);
		}
		return static::$instance;
	}

	/**
	 * Method to update data after update in db.
	 */
	public static function updateData(): void
	{
		static::$instance->setData((new Query())->select(['name', 'value'])->from(self::TABLE_NAME)->createCommand()->queryAllByGroup());
	}

	/**
	 * Save in db last scanned id.
	 *
	 * @param string      $type
	 * @param bool|string $name
	 * @param bool|int    $id
	 *
	 * @throws \yii\db\Exception
	 */
	public static function setScan(string $type, $name = false, $id = false): void
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (false !== $name) {
			$data = [
				'name' => "{$type}_last_scan_{$name}",
				'value' => $id
			];
		} else {
			$data = [
				'name' => $type . '_start_scan_date',
				'value' => date('Y-m-d H:i:s')
			];
		}
		if (!(new Query())->from(self::TABLE_NAME)
			->where(['name' => $data['name']])
			->exists()) {
			$dbCommand->insert(self::TABLE_NAME, $data)->execute();
		} else {
			$dbCommand->update(self::TABLE_NAME, $data, ['name' => $data['name']])->execute();
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
			if (!(new Query())->from(self::TABLE_NAME)
				->where(['name' => $data['name']])
				->exists()) {
				$dbCommand->insert(self::TABLE_NAME, $data)->execute();
			} else {
				$dbCommand->update(self::TABLE_NAME, $data, ['name' => $data['name']])->execute();
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
		$instance = self::getInstance();
		return [
			'id' => $instance->get($type . '_last_scan_id') ?? 0,
			'idcrm' => $instance->get($type . '_last_scan_idcrm') ?? 0,
			'idmap' => $instance->get($type . '_last_scan_idmap') ?? 0,
			'start_date' => $instance->get($type . '_start_scan_date') ?? false,
			'end_date' => $instance->get($type . '_end_scan_date') ?? false,
		];
	}
}

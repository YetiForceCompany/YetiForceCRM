<?php

/**
 * Settings PublicHoliday module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_PublicHoliday_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Delete holiday.
	 *
	 * @param <Int> $id - id of holiday
	 *
	 * @return - true on success, false on failure
	 */
	public static function delete($id)
	{
		$deleted = App\Db::getInstance()->createCommand()
			->delete('vtiger_publicholiday', ['publicholidayid' => $id])
			->execute();
		\App\Cache::clear();
		if (1 === $deleted) {
			return true;
		}
		return false;
	}

	/**
	 * Add new holiday.
	 *
	 * @param string $date - date of the holiday
	 * @param string $name - name of the holiday
	 * @param string $type - type of the holiday
	 *
	 * @return - true on success, false on failure
	 */
	public static function save($date, $name, $type)
	{
		$saved = App\Db::getInstance()->createCommand()
			->insert('vtiger_publicholiday', [
				'holidaydate' => $date,
				'holidayname' => $name,
				'holidaytype' => $type,
			])->execute();
		\App\Cache::clear();
		if (1 === $saved) {
			return true;
		}
		return false;
	}

	/**
	 * Edit holiday.
	 *
	 * @param <Int>  $id   - id of the holiday
	 * @param string $date - date of the holiday
	 * @param string $name - name of the holiday
	 * @param string $type - name of the holiday
	 *
	 * @return - true on success, false on failure
	 */
	public static function edit($id, $date, $name, $type)
	{
		$saved = App\Db::getInstance()->createCommand()
			->update('vtiger_publicholiday', [
				'holidaydate' => $date,
				'holidayname' => $name,
				'holidaytype' => $type,
			], ['publicholidayid' => $id])
			->execute();
		\App\Cache::clear();
		if (1 === $saved) {
			return true;
		}
		return false;
	}

	/**
	 * @param <array> $date - start and end date to get holidays
	 *
	 * @return - holidays count group by type if exist or false
	 */
	public static function getHolidayGroupType($date = false)
	{
		$query = (new App\Db\Query())
			->select(['count' => new \yii\db\Expression('COUNT(publicholidayid)'), 'holidaytype'])
			->from('vtiger_publicholiday');

		if ($date) {
			$date[0] = DateTimeField::convertToDBFormat($date[0]);
			$date[1] = DateTimeField::convertToDBFormat($date[1]);
			$query->where(['between', 'holidaydate', $date[0], $date[1]]);
		}
		$query->groupBy('holidaytype');
		$dataReader = $query->createCommand()->query();
		if (0 === $dataReader->count()) {
			$return = false;
		} else {
			while ($row = $dataReader->read()) {
				$return[$row['holidaytype']] = $row['count'];
			}
		}
		$dataReader->close();
		return $return;
	}
}

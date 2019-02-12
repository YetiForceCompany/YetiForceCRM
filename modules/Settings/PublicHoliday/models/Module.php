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
	 * Gets list of holidays.
	 *
	 * @param string $dateStart - beginning date
	 * @param string $dateTo    - ending date
	 *
	 * @return - array of holidays success, false on failure
	 */
	public static function getHolidays($date)
	{
		\App\Log::trace('Entering Settings_PublicHoliday_Module_Model::getHolidays(' . print_r($date, true) . ') method ...');

		$query = (new App\Db\Query())->select(['publicholidayid', 'holidaydate', 'holidayname', 'holidaytype'])
			->from('vtiger_publicholiday');
		$date[0] = DateTimeField::convertToDBFormat($date[0]);
		$date[1] = DateTimeField::convertToDBFormat($date[1]);
		if (is_array($date)) {
			$query->where(['between', 'holidaydate', $date[0], $date[1]]);
		}
		$query->orderBy(['holidaydate' => SORT_ASC]);
		$holidays = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$id = $row['publicholidayid'];
			$date = $row['holidaydate'];
			$name = $row['holidayname'];
			$type = $row['holidaytype'];
			$holidays[$id]['id'] = $id;
			$holidays[$id]['date'] = $date;
			$holidays[$id]['name'] = $name;
			$holidays[$id]['type'] = $type;
			$holidays[$id]['day'] = \App\Language::translate(date('l', strtotime($date)), 'PublicHoliday');
		}
		$dataReader->close();
		\App\Log::trace('Exiting Settings_PublicHoliday_Module_Model::getHolidays() method ...');

		return $holidays;
	}

	/**
	 * Delete holiday.
	 *
	 * @param <Int> $id - id of holiday
	 *
	 * @return - true on success, false on failure
	 */
	public static function delete($id)
	{
		\App\Log::trace('Entering Settings_PublicHoliday_Module_Model::delete(' . $id . ') method ...');
		$deleted = App\Db::getInstance()->createCommand()
			->delete('vtiger_publicholiday', ['publicholidayid' => $id])
			->execute();
		\App\Log::trace('Exiting Settings_PublicHoliday_Module_Model::delete() method ...');
		if ($deleted === 1) {
			return true;
		} else {
			return false;
		}
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
		\App\Log::trace('Entering Settings_PublicHoliday_Module_Model::save(' . $date . ', ' . $name . ', ' . $type . ') method ...');
		$saved = App\Db::getInstance()->createCommand()
			->insert('vtiger_publicholiday', [
				'holidaydate' => $date,
				'holidayname' => $name,
				'holidaytype' => $type,
			])->execute();
		\App\Log::trace('Exiting Settings_PublicHoliday_Module_Model::save() method ...');
		if ($saved === 1) {
			return true;
		} else {
			return false;
		}
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
		\App\Log::trace('Entering Settings_PublicHoliday_Module_Model::edit(' . $id . ', ' . $date . ', ' . $name . ', ' . $type . ') method ...');
		$saved = App\Db::getInstance()->createCommand()
			->update('vtiger_publicholiday', [
				'holidaydate' => $date,
				'holidayname' => $name,
				'holidaytype' => $type,
			], ['publicholidayid' => $id])
			->execute();
		\App\Log::trace('Exiting Settings_PublicHoliday_Module_Model::edit() method ...');
		if ($saved === 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param <array> $date - start and end date to get holidays
	 *
	 * @return - holidays count group by type if exist or false
	 */
	public static function getHolidayGroupType($date = false)
	{
		\App\Log::trace('Entering Settings_PublicHoliday_Module_Model::getHolidayGroupType method ...');
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
		\App\Log::trace('Exiting Settings_PublicHoliday_Module_Model::getHolidayGroupType() method ...');

		return $return;
	}
}

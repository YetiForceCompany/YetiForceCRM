<?php

/**
 * Settings calendar module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Calendar_Module_Model extends Settings_Vtiger_Module_Model
{
	public static function getCalendarConfig($type)
	{
		$query = (new \App\Db\Query())
			->from('vtiger_calendar_config')
			->where(['type' => $type]);
		$dataReader = $query->createCommand()->query();
		$calendarConfig = [];
		while ($row = $dataReader->read()) {
			$calendarConfig[] = [
				'name' => $row['name'],
				'label' => $row['label'],
				'value' => $row['value'],
			];
		}
		$dataReader->close();
		if ('colors' == $type) {
			$calendarConfig = array_merge($calendarConfig, self::getPicklistValue());
		}
		return $calendarConfig;
	}

	public static function updateCalendarConfig($params)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_calendar_config', ['value' => $params['color']], ['name' => $params['id']])
			->execute();
		\App\Cache::clear();
	}

	/**
	 * Updates working days.
	 *
	 * @param array $params
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return void
	 */
	public static function updateNotWorkingDays(array $params)
	{
		if (!empty($params['val'])) {
			$value = implode(';', $params['val']);
		} else {
			$value = '';
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_calendar_config', ['value' => $value], ['name' => 'notworkingdays']
		)->execute();
	}

	/**
	 * Get not working days.
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return array
	 */
	public static function getNotWorkingDays()
	{
		$query = (new \App\Db\Query())
			->from('vtiger_calendar_config')
			->where(['name' => 'notworkingdays']);
		$row = $query->createCommand()->queryOne();
		$return = [];
		if (!empty($row['value'])) {
			$return = explode(';', $row['value']);
		}
		return $return;
	}

	public static function getCalendarColorPicklist()
	{
		return ['activitytype'];
	}

	/**
	 * Get picklist values.
	 *
	 * @return array
	 */
	public static function getPicklistValue()
	{
		$keys = ['name', 'label', 'value', 'table', 'field'];
		$calendarConfig = [];
		foreach (self::getCalendarColorPicklist() as $picklistName) {
			$picklistValues = \App\Fields\Picklist::getValues($picklistName);
			foreach ($picklistValues as $picklistValueId => $picklistValue) {
				if (false === strpos($picklistValue['color'], '#')) {
					$picklistValue['color'] = '#' . $picklistValue['color'];
				}
				$calendarConfig[] = array_combine($keys, [
					'id' => $picklistValueId,
					'value' => $picklistValue[$picklistName],
					'color' => $picklistValue['color'],
					'table' => 'vtiger_' . $picklistName,
					'field' => $picklistName, ]);
			}
		}
		return $calendarConfig;
	}
}

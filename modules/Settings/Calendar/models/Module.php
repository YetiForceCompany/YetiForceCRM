<?php

/**
 * Settings calendar module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_Calendar_Module_Model extends Settings_Vtiger_Module_Model
{

	public static function getUserColors()
	{
		$instance = new \App\Fields\Owner();
		$users = $instance->initUsers();

		$calendarViewTypes = [];
		foreach ($users as $id => &$user) {
			$calendarViewTypes[] = [
				'id' => $id,
				'first' => $user['first_name'],
				'last' => $user['last_name'],
				'color' => $user['cal_color']
			];
		}
		return $calendarViewTypes;
	}

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
				'value' => $row['value']
			];
		}
		if ($type == 'colors') {
			$calendarConfig = array_merge($calendarConfig, self::getPicklistValue());
		}
		return $calendarConfig;
	}

	public static function updateCalendarConfig($params)
	{
		if ($params['table']) {
			Users_Colors_Model::updateColor($params);
		} else {
			\App\Db::getInstance()->createCommand()->update('vtiger_calendar_config', ['value' => $params['color']], ['name' => $params['id']]
			)->execute();
		}
		\App\Cache::clear();
		\App\Colors::generate('calendar');
	}

	public static function updateNotWorkingDays($params)
	{
		if (!empty($params['val'])) {
			$value = implode(';', $params['val']);
		} else {
			$value = NULL;
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_calendar_config', ['value' => $value], ['name' => 'notworkingdays']
		)->execute();
	}

	public static function getNotWorkingDays()
	{
		$query = (new \App\Db\Query())
			->from('vtiger_calendar_config')
			->where(['name' => 'notworkingdays']);
		$row = $query->createCommand()->queryOne();
		$return = [];
		if (isset($row['value']))
			$return = explode(';', $row['value']);

		return $return;
	}

	public static function getCalendarColorPicklist()
	{
		return ['activitytype'];
	}

	/**
	 * Get picklist values
	 * @return array
	 */
	public static function getPicklistValue()
	{
		$keys = ['name', 'label', 'value', 'table', 'field'];
		$calendarConfig = [];
		foreach (self::getCalendarColorPicklist() as $picklistName) {
			$picklistValues = \App\Fields\Picklist::getValues($picklistName);
			foreach ($picklistValues as $picklistValueId => $picklistValue) {
				if (strpos($picklistValue['color'], '#') === false) {
					$picklistValue['color'] = '#' . $picklistValue['color'];
				}
				$calendarConfig[] = array_combine($keys, [
					'id' => $picklistValueId,
					'value' => $picklistValue[$picklistName],
					'color' => $picklistValue['color'],
					'table' => 'vtiger_' . $picklistName,
					'field' => $picklistName]);
			}
		}
		return $calendarConfig;
	}
}

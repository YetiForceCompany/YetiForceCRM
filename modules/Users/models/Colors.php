<?php

/**
 * Settings users colors model  class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Users_Colors_Model extends Vtiger_Record_Model
{

	public static function getUserColors()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->query('SELECT * FROM vtiger_users');

		$userColors = [];
		while ($activityTypes = $adb->getRow($result)) {
			$userColors[] = array(
				'id' => $activityTypes['id'],
				'first' => $activityTypes['first_name'],
				'last' => $activityTypes['last_name'],
				'color' => $activityTypes['cal_color']
			);
		}
		return $userColors;
	}

	public static function updateUserColor($params)
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery('UPDATE vtiger_users SET cal_color = ? WHERE id = ?;', array($params['color'], $params['id']));
		\App\Colors::generate('user');
	}

	public static function generateColor($params)
	{
		$color = \App\Colors::getRandomColor();
		$params['color'] = $color;
		switch ($params['mode']) {
			case 'generateGroupColor':
				self::updateGroupColor($params);
				break;
			case 'generateColorForProcesses':
				self::updateColor($params);
				break;
			case 'generateModuleColor':
				self::updateModuleColor($params);
				break;
			default:
				self::updateUserColor($params);
				break;
		}
		return $color;
	}

	public static function getGroupColors()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->query('SELECT * FROM vtiger_groups');

		$groupColors = [];
		while ($activityTypes = $adb->getRow($result)) {
			$groupColors[] = array(
				'id' => $activityTypes['groupid'],
				'groupname' => $activityTypes['groupname'],
				'color' => $activityTypes['color']
			);
		}
		return $groupColors;
	}

	public static function updateGroupColor($params)
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery('UPDATE vtiger_groups SET color = ? WHERE groupid = ?;', array($params['color'], $params['id']));
	}

	public static function updateColor($params)
	{
		$primaryKey = App\Fields\Picklist::getPickListId($params['field']);
		App\Db::getInstance()->createCommand()
			->update($params['table'], ['color' => $params['color']], [$primaryKey => $params['id']])
			->execute();
	}

	public static function getValuesFromField($fieldName)
	{
		$primaryKey = App\Fields\Picklist::getPickListId($fieldName);
		$dataReader = (new \App\Db\Query)->from('vtiger_' . $fieldName)->orderBy('sortorderid')->createCommand()->query();
		$groupColors = [];
		while ($row = $dataReader->read()) {
			$groupColors[] = [
				'id' => $row[$primaryKey],
				'value' => App\Purifier::decodeHtml(App\Purifier::decodeHtml($row[$fieldName])),
				'color' => $row['color']
			];
		}
		return $groupColors;
	}

	public static function getModulesColors($active = false)
	{
		$allModules = \vtlib\Functions::getAllModules(false, false, false, $active);

		$modules = [];
		foreach ($allModules as $tabid => $module) {
			$modules[] = array(
				'id' => $tabid,
				'module' => $module['name'],
				'color' => $module['color'] != '' ? '#' . $module['color'] : '',
				'active' => $module['coloractive'],
			);
		}
		return $modules;
	}

	public static function activeColor($params)
	{
		$colorActive = $params['status'] == 'true' ? 1 : 0;
		if ($params['color'] === '') {
			$color = \App\Colors::getRandomColor();
			$set = ['color' => str_replace("#", "", $color), 'coloractive' => $colorActive];
		} else {
			$set = ['coloractive' => $colorActive];
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_tab', $set, ['tabid' => $params['id']])->execute();
		return $color;
	}

	/**
	 * Function to update color for module
	 * @param array $params
	 */
	public static function updateModuleColor($params)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['color' => str_replace('#', '', $params['color'])], ['tabid' => $params['id']])->execute();
	}
}

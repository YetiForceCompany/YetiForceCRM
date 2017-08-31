<?php

/**
 * Settings users colors model  class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Users_Colors_Model extends Vtiger_Record_Model
{

	public static function generateColor($params)
	{
		$color = \App\Colors::getRandomColor();
		$params['color'] = $color;
		switch ($params['mode']) {
			case 'generateColorForProcesses':
				self::updateColor($params);
				break;
		}
		return $color;
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
}

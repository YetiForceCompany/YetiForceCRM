<?php

/**
 * Settings users colors model  class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Users_Colors_Model extends Vtiger_Record_Model
{

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
}

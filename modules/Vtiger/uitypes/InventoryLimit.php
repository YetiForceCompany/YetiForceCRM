<?php

/**
 * UIType InventoryLimit Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author YetiForce.com
 */
class Vtiger_InventoryLimit_UIType extends Vtiger_Picklist_UIType
{

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $value
	 * @param int $record
	 * @param Vtiger_Record_Model $recordInstance
	 * @param bool $rawText
	 * @return string
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$limits = $this->getPicklistValues();
		return isset($limits[$value]) ? $limits[$value] : '';
	}

	/**
	 * Function to get credit limits
	 * @param int $value
	 * @return array
	 */
	public static function getValues($value)
	{
		$limits = self::getLimits();
		return isset($limits[$value]) ? $limits[$value] : [];
	}

	/**
	 * Function to get all credit limits
	 * @return array
	 */
	public static function getLimits()
	{
		if (\App\Cache::has('Inventory', 'CreditLimits')) {
			return \App\Cache::get('Inventory', 'CreditLimits');
		}
		$limits = (new App\Db\Query())->from('a_#__inventory_limits')->where(['status' => 0])
				->createCommand(App\Db::getInstance('admin'))->queryAllByGroup(1);
		\App\Cache::save('Inventory', 'CreditLimits', $limits, \App\Cache::LONG);
		return $limits;
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return array List of picklist values if the field
	 */
	public function getPicklistValues()
	{
		$limits = self::getLimits();
		foreach ($limits as $key => $limit) {
			$limits[$key] = $limit['value'] . ' - ' . $limit['name'];
		}
		return $limits;
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (is_array($value)) {
			$value = implode(',', $value);
		}
		return $value;
	}
}

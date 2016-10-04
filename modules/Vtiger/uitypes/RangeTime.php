<?php

/**
 * UIType RangeTime Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_RangeTime_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return $value
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$isNull = is_null($value);
		$result = vtlib\Functions::getRangeTime($value, !$isNull);
		$mode = $this->get('field')->getFieldParams();
		if (empty($mode)) {
			$mode = 'short';
		}
		return $result[$mode];
	}

	public function isActiveSearchView()
	{
		return false;
	}
}

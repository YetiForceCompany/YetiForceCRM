<?php

/**
 * 
 * @package YetiForce.uitypes
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSTimeControl_Double_UIType extends Vtiger_Double_UIType
{

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		if ($this->get('field')->getFieldName() == 'sum_time') {
			$return = vtlib\Functions::decimalTimeFormat($value);
			return $return['short'];
		} else {
			return \vtlib\Functions::formatDecimal($value);
		}
	}
}

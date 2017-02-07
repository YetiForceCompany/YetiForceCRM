<?php

/**
 * UIType total time field class
 * @package YetiForce.UIType
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TotalTime_UIType extends Vtiger_Double_UIType
{

	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$return = vtlib\Functions::decimalTimeFormat($value);
		return $return['short'];
	}
}

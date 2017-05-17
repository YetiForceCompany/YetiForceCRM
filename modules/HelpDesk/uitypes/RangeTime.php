<?php

/**
 * UIType RangeTime Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class HelpDesk_RangeTime_UIType extends Vtiger_RangeTime_UIType
{

	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$isNull = is_null($value);
		if ($this->get('field')->getName() == 'response_time') {
			$value = round(vtlib\Functions::getDateTimeMinutesDiff($value, date('Y-m-d H:i:s')));
		}
		$result = vtlib\Functions::getRangeTime($value, !$isNull);
		$mode = $this->get('field')->getFieldParams();
		if (empty($mode)) {
			$mode = 'short';
		}
		return $result[$mode];
	}
}

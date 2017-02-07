<?php

/**
 * UIType Company Field Class
 * @package YetiForce.UIType
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TotalTime_UIType extends Vtiger_Double_UIType
{

	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$h = intval($value);
		$m = round((((($value - $h) / 100.0) * 60.0) * 100), 0);
		if ($m === 60) {
			$h++;
			$m = 0;
		}
		return $h . ' ' . App\Language::translate('LBL_HOURS') . ' ' . $m . ' ' . App\Language::translate('LBL_MINUTES');
	}
}

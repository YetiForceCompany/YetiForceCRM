<?php

/**
 * UIType total time field class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TotalTime_UIType extends Vtiger_Double_UIType
{
	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$params = $this->getFieldModel()->getFieldParams();
		$formatOut = $params['formatOut'] ?? 'hi';
		return \App\Fields\RangeTime::displayElapseTime($value, 'i', $formatOut);
	}
}

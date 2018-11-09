<?php

/**
 * UIType total time field class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TotalTime_UIType extends Vtiger_Double_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		return \App\Fields\Time::formatToHourText($value, 'short');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'];
	}
}

<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSTimeControl_Double_UIType extends Vtiger_Double_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($this->get('field')->getFieldName() === 'sum_time') {
			return \App\Fields\Time::formatToHourText((float) $value, 'short');
		} else {
			return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
		}
	}
}

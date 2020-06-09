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
		if ('sum_time' === $this->get('field')->getFieldName()) {
			return \App\Fields\RangeTime::formatHourToDisplay((float) $value, 'short');
		}
		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}
}

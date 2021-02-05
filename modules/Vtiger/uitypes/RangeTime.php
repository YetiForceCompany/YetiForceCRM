<?php

/**
 * UIType RangeTime Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RangeTime_UIType extends Vtiger_Integer_UIType
{
	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$mode = 'short';
		$unit = 'h';
		$params = $this->getFieldModel()->getFieldParams();
		if (isset($params['mode'])) {
			$mode = $params['mode'];
		}
		if (isset($params['unit'])) {
			$unit = $params['unit'];
		}
		return \App\Purifier::encodeHtml(App\Fields\RangeTime::formatToRangeText($value, $mode, null !== $value, $unit));
	}

	/** {@inheritdoc} */
	public function isActiveSearchView()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['integer'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['y', 'ny'];
	}
}

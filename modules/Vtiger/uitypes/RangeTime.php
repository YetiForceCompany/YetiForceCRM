<?php

/**
 * UIType RangeTime Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RangeTime_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$mode = $this->getFieldModel()->getFieldParams();
		if (empty($mode)) {
			$mode = 'short';
		}
		return \App\Purifier::encodeHtml(App\Fields\RangeTime::formatToRangeText($value, $mode, null !== $value));
	}

	/**
	 * {@inheritdoc}
	 */
	public function isActiveSearchView()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['y', 'ny'];
	}
}

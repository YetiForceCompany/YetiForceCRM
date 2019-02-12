<?php

/**
 * UIType RangeTime Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_RangeTime_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$result = vtlib\Functions::getRangeTime($value, !is_null($value));
		$mode = $this->getFieldModel()->getFieldParams();
		if (empty($mode)) {
			$mode = 'short';
		}
		return \App\Purifier::encodeHtml($result[$mode]);
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

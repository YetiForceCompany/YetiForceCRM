<?php
/**
 * UIType Integer Field Class for CallHistory.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * UIType Integer Field Class for CallHistory.
 */
class CallHistory_Integer_UIType extends Vtiger_Integer_UIType
{
	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		if ('duration' === $this->getFieldModel()->getName() && $recordModel) {
			return $value ? \App\Fields\RangeTime::displayElapseTime($value, 's', 'his') : 0;
		}
		return parent::getListViewDisplayValue($value, $record, $recordModel, $rawText);
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ('duration' === $this->getFieldModel()->getName() && $recordModel) {
			return $value ? \App\Fields\RangeTime::displayElapseTime($value, 's', 'his') : 0;
		}
		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}
}

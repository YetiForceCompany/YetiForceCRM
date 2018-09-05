<?php
/**
 * UIType Integer Field Class for Documents.
 *
 * @package   UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * UIType Integer Field Class for Documents.
 */
class Documents_Integer_UIType extends Vtiger_Integer_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		if ($this->getFieldModel()->getName() === 'filesize' && $recordModel) {
			return $value ? vtlib\Functions::showBytes($value) : '-';
		}
		return parent::getListViewDisplayValue($value, $record, $recordModel, $rawText);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($this->getFieldModel()->getName() === 'filesize' && $recordModel) {
			return $value ? vtlib\Functions::showBytes($value) : '-';
		}
		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}
}

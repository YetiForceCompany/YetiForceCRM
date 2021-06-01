<?php
/**
 * Account name UIType field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Account name Base UIType field class.
 */
class Vtiger_AccountName_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (0 === strpos($value, '|##|')) {
			$value = str_replace('|##|', '', $value);
		} else {
			$value = str_replace('|##|', ' ', $value);
		}
		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/AccountName.tpl';
	}

	/**
	 * Parse account name for first and last name.
	 *
	 * @return string[]
	 */
	public function parseName(): array
	{
		$exploded = explode('|##|', $this->getFieldModel()->get('fieldvalue'), 2);
		return ['first' => isset($exploded[1]) ? $exploded[0] : '', 'last' => $exploded[1] ?? $exploded[0]];
	}
}

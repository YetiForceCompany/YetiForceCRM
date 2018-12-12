<?php

/**
 * Inventory Unit Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Vtiger_Unit_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Unit';
	protected $defaultLabel = 'LBL_UNIT';
	protected $columnName = 'unit';
	protected $dbType = 'string';
	protected $onlyOne = true;

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $rawText = false, $related = '')
	{
		if ($mapDetail = $this->getMapDetail($related)) {
			$value = $mapDetail->getDisplayValue($value, false, false, $rawText);
		}
		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditValue($value)
	{
		return \App\Purifier::encodeHtml($value);
	}
}

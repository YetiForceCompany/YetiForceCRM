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
	protected $purifyType = \App\Purifier::TEXT;

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (($rel = $rowData['name'] ?? '') && ($mapDetail = $this->getMapDetail(\App\Record::getType($rel)))) {
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

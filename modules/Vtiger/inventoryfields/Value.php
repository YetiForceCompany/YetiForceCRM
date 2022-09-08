<?php

/**
 * Inventory Value Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Value_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Value';
	protected $defaultLabel = 'LBL_STRING';
	protected $columnName = 'value';
	protected $dbType = 'string';
	protected $onlyOne = false;
	protected $purifyType = \App\Purifier::TEXT;

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (($rel = $rowData['name'] ?? '') && (($module = \App\Record::getType($rel)) && $mapDetail = $this->getMapDetail($module))) {
			$value = $mapDetail->getDisplayValue($value, false, false, $rawText);
		}
		return $value;
	}
}

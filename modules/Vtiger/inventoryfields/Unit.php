<?php

/**
 * Inventory Unit Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Vtiger_Unit_InventoryField extends Vtiger_Basic_InventoryField
{
	/** {@inheritdoc} */
	protected $type = 'Unit';
	/** {@inheritdoc} */
	protected $defaultLabel = 'LBL_UNIT';
	/** {@inheritdoc} */
	protected $columnName = 'unit';
	/** {@inheritdoc} */
	protected $dbType = 'string';
	/** {@inheritdoc} */
	protected $onlyOne = true;
	/** {@inheritdoc} */
	protected $purifyType = \App\Purifier::TEXT;

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (($rel = $rowData['name'] ?? '') && (($type = \App\Record::getType($rel)) && $mapDetail = $this->getMapDetail($type))) {
			$value = $mapDetail->getDisplayValue($value, false, false, $rawText);
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getEditValue($value)
	{
		return \App\Purifier::encodeHtml($value);
	}
}

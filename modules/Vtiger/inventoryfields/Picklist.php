<?php

/**
 * Inventory Picklist Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Picklist_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Picklist';
	protected $defaultLabel = 'LBL_PICKLIST';
	protected $columnName = 'picklist';
	protected $onlyOne = false;
	protected $purifyType = \App\Purifier::TEXT;
	protected $params = ['values'];

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return $value ? \App\Language::translate($value, $this->getModuleName(), null, !$rawText) : '';
	}

	public function getPicklistValues()
	{
		$values = $this->getParamsConfig()['values'] ?? [];
		if (\is_string($values)) {
			$values = explode(' |##| ', $values);
		}

		return $values;
	}

	/** {@inheritdoc} */
	public function getConfigFieldsData(): array
	{
		$data = parent::getConfigFieldsData();
		$data['values'] = [
			'name' => 'values',
			'label' => 'LBL_PICKLIST_VALUES',
			'uitype' => 33,
			'maximumlength' => '6500',
			'typeofdata' => 'V~M',
			'purifyType' => \App\Purifier::TEXT,
			'createTags' => true,
			'picklistValues' => [],
		];
		foreach ($this->getPicklistValues() as $value) {
			$data['values']['picklistValues'][$value] = \App\Language::translate($value, $this->getModuleName(), null, false);
		}

		return $data;
	}
}

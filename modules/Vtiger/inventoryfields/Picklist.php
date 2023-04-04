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
	protected $searchable = true;
	/** {@inheritdoc} */
	protected $queryOperators = ['e', 'n', 'y', 'ny'];
	/** {@inheritdoc} */
	protected $recordOperators = ['e', 'n', 'y', 'ny'];

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
			$values = array_combine($values, $values);
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

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/Picklist.tpl';
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$values = [];
		if (!\is_array($value)) {
			$value = $value ? explode('##', $value) : [];
		}
		foreach ($value as $val) {
			$values[] = parent::getDbConditionBuilderValue($val, $operator);
		}
		return implode('##', $values);
	}
}

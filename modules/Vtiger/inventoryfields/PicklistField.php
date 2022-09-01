<?php

/**
 * Inventory Picklist from Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_PicklistField_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'PicklistField';
	protected $defaultLabel = 'LBL_PICKLIST_FIELD';
	protected $columnName = 'picklistfield';
	protected $dbType = 'string';
	protected $onlyOne = false;
	protected $purifyType = \App\Purifier::TEXT;

	/** {@inheritdoc} */
	public function getParams()
	{
		$params = [];
		$inventory = Vtiger_Inventory_Model::getInstance($this->getModuleName());
		if ($field = $inventory->getField('name')) {
			$params = $field->getModules();
		}
		return $params;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		$moduleName = !empty($rowData['name']) ? \App\Record::getType($rowData['name']) : $this->getModuleName();
		return $value ? \App\Language::translate($value, $moduleName, null, !$rawText) : '';
	}

	public function getPicklist($moduleName)
	{
		$values = [];
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		foreach ($moduleModel->getFieldsByType(['picklist']) as $fieldName => $fieldModel) {
			$values[$fieldName] = \App\Language::translate($fieldModel->get('label'), $moduleName);
		}
		return $values;
	}

	/**
	 * Gets picklist values.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public function getPicklistValues(string $moduleName): array
	{
		$modules = $this->getParamsConfig();
		if ($moduleName) {
			foreach ($modules as $module => $field) {
				if ($module != $moduleName) {
					unset($modules[$module]);
				}
			}
		}
		$values = [];
		foreach ($modules as $module => $field) {
			foreach (App\Fields\Picklist::getValuesName($field) as $value) {
				$values[] = [
					'module' => $module,
					'value' => $value,
					'name' => \App\Language::translate($value, $module, false, false),
				];
			}
		}
		return $values;
	}

	/** {@inheritdoc} */
	public function getConfigFieldsData(): array
	{
		$data = parent::getConfigFieldsData();

		foreach ($this->getParams() as $moduleName) {
			$data[$moduleName] = [
				'name' => $moduleName,
				'label' => \App\Language::translate($moduleName, $moduleName, false, false),
				'uitype' => 16,
				'maximumlength' => '6500',
				'typeofdata' => 'V~M',
				'purifyType' => \App\Purifier::TEXT,
				'picklistValues' => [],
			];
			foreach ($this->getPicklist($moduleName) as $key => $value) {
				$data[$moduleName]['picklistValues'][$key] = $value;
			}
		}

		return $data;
	}
}

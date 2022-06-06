<?php

 /* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_Picklist_Module_Model extends Settings_Vtiger_Module_Model
{
	/** @var string Module name */
	public $name = 'Picklist';
	/** @var string Parent name */
	public $parent = 'Settings';
	/** @var \Vtiger_Module_Model Source module model */
	public $sourceModule;
	/** @var \Settings_Picklist_Field_Model[] Fields model */
	protected $fields = [];

	/**
	 * Set source module.
	 *
	 * @param string $sourceModule
	 *
	 * @return $this
	 */
	public function setSourceModule(string $sourceModule)
	{
		$this->sourceModule = \Vtiger_Module_Model::getInstance($sourceModule);

		return $this;
	}

	/** {@inheritdoc} */
	public function getFieldsByType($type, bool $active = false): array
	{
		if (!$this->fields) {
			$fieldModels = $this->sourceModule->getFieldsByType($type, $active);
			foreach ($fieldModels as $fieldName => $fieldModel) {
				$field = Settings_Picklist_Field_Model::getInstanceFromFieldObject($fieldModel);
				$this->fields[$fieldName] = $field;
			}
		}

		return $this->fields;
	}

	/**
	 * Get fields instance by name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldByName($name)
	{
		return $this->getFieldsByType(['picklist', 'multipicklist'], true)[$name] ?? null;
	}

	/**
	 * Enable or disable values for role.
	 *
	 * @param string   $picklistFieldName
	 * @param string[] $valuesToEnables
	 * @param string[] $valuesToDisable
	 * @param string[] $roleIdList
	 *
	 * @return void
	 */
	public function enableOrDisableValuesForRole($picklistFieldName, $valuesToEnables, $valuesToDisable, $roleIdList)
	{
		$db = App\Db::getInstance();
		$picklistId = (new App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')
			->where(['name' => $picklistFieldName])->scalar();
		$primaryKey = App\Fields\Picklist::getPickListId($picklistFieldName);
		$pickListValueList = array_merge($valuesToEnables, $valuesToDisable);
		$dataReader = (new App\Db\Query())->select(['picklist_valueid', $picklistFieldName, $primaryKey])
			->from(\App\Fields\Picklist::getPickListTableName($picklistFieldName))
			->where([$primaryKey => $pickListValueList])
			->createCommand()->query();
		$pickListValueDetails = [];
		while ($row = $dataReader->read()) {
			$pickListValueDetails[App\Purifier::decodeHtml($row[$primaryKey])] = $row['picklist_valueid'];
		}
		$dataReader->close();
		if ($pickListValueDetails && $pickListValueList) {
			$insertValueList = [];
			$deleteValueList = [];
			foreach ($roleIdList as $roleId) {
				foreach ($valuesToEnables as $picklistValue) {
					if (empty($pickListValueDetails[$picklistValue])) {
						$pickListValueId = $pickListValueDetails[App\Purifier::encodeHtml($picklistValue)];
					} else {
						$pickListValueId = $pickListValueDetails[$picklistValue];
					}
					$insertValueList[] = [$roleId, $pickListValueId, $picklistId];
					$deleteValueList[] = ['roleid' => $roleId, 'picklistvalueid' => $pickListValueId];
				}
				foreach ($valuesToDisable as $picklistValue) {
					if (empty($pickListValueDetails[$picklistValue])) {
						$pickListValueId = $pickListValueDetails[App\Purifier::encodeHtml($picklistValue)];
					} else {
						$pickListValueId = $pickListValueDetails[$picklistValue];
					}
					$deleteValueList[] = ['roleid' => $roleId, 'picklistvalueid' => $pickListValueId];
				}
			}
			if ($deleteValueList) {
				array_unshift($deleteValueList, 'or');
				$db->createCommand()->delete('vtiger_role2picklist', $deleteValueList)->execute();
			}
			$db->createCommand()->batchInsert('vtiger_role2picklist', ['roleid', 'picklistvalueid', 'picklistid'], $insertValueList)->execute();
		}
	}

	/**
	 * Function to update sequence number.
	 *
	 * @param string $pickListFieldName
	 * @param array  $picklistValues
	 */
	public function updateSequence($pickListFieldName, $picklistValues)
	{
		$db = \App\Db::getInstance();
		$primaryKey = App\Fields\Picklist::getPickListId($pickListFieldName);
		$set = ' CASE ';
		foreach ($picklistValues as $values => $sequence) {
			$set .= " WHEN {$db->quoteColumnName($primaryKey)} = {$db->quoteValue($values)} THEN {$db->quoteValue($sequence)}";
		}
		$set .= ' END';
		$expression = new \yii\db\Expression($set);
		$db->createCommand()->update(\App\Fields\Picklist::getPickListTableName($pickListFieldName), ['sortorderid' => $expression])->execute();
	}

	/**
	 * Function to get the instance of Vtiger Module Model from a given vtlib\Module object.
	 *
	 * @param vtlib\Module $moduleObj
	 *
	 * @return self instance
	 */
	public static function getInstanceFromModuleObject(vtlib\Module $moduleObj): self
	{
		$objectProperties = get_object_vars($moduleObj);
		$moduleModel = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$moduleModel->{$properName} = $propertyValue;
		}
		return $moduleModel;
	}

	/**
	 * list of modules in which they appear picklist fields.
	 *
	 * @param array $pickListFields
	 *
	 * @return array
	 */
	public function listModuleInterdependentPickList(array $pickListFields): array
	{
		$interdependent = [];
		$dataReader = (new App\Db\Query())->select(['tabid', 'fieldname'])
			->from('vtiger_field')
			->where(['fieldname' => $pickListFields])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$moduleName = \App\Module::getModuleName($row['tabid']);
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			if ($moduleModel->isActive() && $moduleModel->getFieldByName($row['fieldname'])->isActiveField()) {
				$interdependent[$row['fieldname']][] = \App\Language::translate($moduleName, $moduleName);
			}
		}
		return $interdependent;
	}
}

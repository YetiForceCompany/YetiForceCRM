<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Settings_Leads_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get fields of this model.
	 *
	 * @param mixed $blockInstance
	 *
	 * @return <Array> list of field models <Settings_Leads_Field_Model>
	 */
	public function getFields($blockInstance = false)
	{
		if (!$this->fields) {
			$fieldModelsList = [];
			$fieldIds = $this->getMappingSupportedFieldIdsList();

			foreach ($fieldIds as $fieldId) {
				$fieldModel = Settings_Leads_Field_Model::getInstance($fieldId, $this);
				$fieldModelsList[$fieldModel->getFieldDataType()][$fieldId] = $fieldModel;
			}
			$this->fields = $fieldModelsList;
		}
		return $this->fields;
	}

	/**
	 * Function to get mapping supported field ids list.
	 *
	 * @return <Array> list of field ids
	 */
	public function getMappingSupportedFieldIdsList()
	{
		if (empty($this->supportedFieldIdsList)) {
			$selectedTabidsList[] = \App\Module::getModuleId($this->getName());
			$presense = [0, 2];
			$restrictedFieldNames = ['campaignrelstatus'];
			$restrictedUitypes = $this->getRestrictedUitypes();
			$selectedGeneratedTypes = [1, 2];
			$dataReader = (new \App\Db\Query())->select(['fieldid'])
				->from('vtiger_field')
				->where([
					'presence' => $presense,
					'tabid' => $selectedTabidsList,
					'generatedtype' => $selectedGeneratedTypes,
				])
				->andWhere(['and', ['NOT IN', 'uitype', $restrictedUitypes], ['NOT IN', 'fieldname', $restrictedFieldNames]])
				->createCommand()->query();
			$this->supportedFieldIdsList = [];
			while ($field = $dataReader->readColumn(0)) {
				$this->supportedFieldIdsList[] = $field;
			}
			$dataReader->close();
		}
		return $this->supportedFieldIdsList;
	}

	/**
	 * Function to get the Restricted Ui Types.
	 *
	 * @return <array> Restricted ui types
	 */
	public function getRestrictedUitypes()
	{
		return [4, 51, 52, 53, 57, 58, 69, 70];
	}

	/**
	 * Function to get instance of module.
	 *
	 * @param string $moduleName
	 *
	 * @return <Settings_Leads_Module_Model>
	 */
	public static function getInstance($moduleName)
	{
		$moduleModel = parent::getInstance($moduleName);
		$objectProperties = get_object_vars($moduleModel);

		$moduleModel = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$moduleModel->{$properName} = $propertyValue;
		}
		return $moduleModel;
	}
}

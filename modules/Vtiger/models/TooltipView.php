<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_TooltipView_Model extends Vtiger_DetailRecordStructure_Model
{
	protected $fields = false;

	/**
	 * Function to set the module instance.
	 *
	 * @param Vtiger_Module_Model $moduleInstance - module model
	 *
	 * @return \self
	 */
	public function setModule($moduleInstance)
	{
		$this->module = $moduleInstance;
		$this->fields = $this->module->getSummaryViewFieldsList();
		if (empty($this->fields)) {
			$this->fields = $this->module->getMandatoryFieldModels();
		}
		return $this;
	}

	/**
	 * Function to get the values in stuctured format.
	 *
	 * @return array - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure()
	{
		if (!$this->structuredValues) {
			$tooltipFieldsList = $this->fields;
			$recordModel = $this->getRecord();
			$this->structuredValues = ['TOOLTIP_FIELDS' => []];
			if ($tooltipFieldsList) {
				foreach ($tooltipFieldsList as $fieldModel) {
					$fieldName = $fieldModel->get('name');
					if ($fieldModel->isViewableInDetailView()) {
						$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						$this->structuredValues['TOOLTIP_FIELDS'][$fieldName] = $fieldModel;
					}
				}
			}
		}
		return $this->structuredValues;
	}

	/**
	 * Function to get the instance.
	 *
	 * @param string $moduleName - module name
	 * @param string $recordId   - record id
	 *
	 * @return \self
	 */
	public static function getInstance($moduleName, $recordId)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TooltipView', $moduleName);
		$instance = new $modelClassName();
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

		return $instance->setModule($recordModel->getModule())->setRecord($recordModel);
	}
}

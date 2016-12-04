<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PBXManager_PBXManagerHandler_Handler
{

	/**
	 * EntityAfterDelete handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$pbxRecordModel = new PBXManager_Record_Model;
		$pbxRecordModel->deletePhoneLookUpRecord($recordModel->getId());
	}

	/**
	 * Entity.AfterSave function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$values = [
			'crmid' => $recordModel->getId(),
			'setype' => $eventHandler->getModuleName(),
		];
		$pbxRecordModel = new PBXManager_Record_Model;
		$moduleInstance = Vtiger_Module_Model::getInstance($eventHandler->getModuleName());
		$fields = $moduleInstance->getFieldsByType('phone');
		foreach ($fields as $fieldName => &$fieldModel) {
			if (!$recordModel->isEmpty($fieldName)) {
				$values[$fieldName] = $recordModel->get($fieldName);
				$pbxRecordModel->receivePhoneLookUpRecord($fieldName, $values, true);
			}
		}
	}
}

class PBXManagerHandler extends VTEventHandler
{

	public function handleEvent($eventName, $entityData)
	{
		$moduleName = $entityData->getModuleName();

		$acceptedModule = array('Contacts', 'Accounts', 'Leads');
		if (!in_array($moduleName, $acceptedModule))
			return;
		if ($eventName == 'vtiger.entity.afterrestore') {
			$this->handlePhoneLookUpRestoreEvent($entityData, $moduleName);
		}
	}

	protected function handlePhoneLookUpRestoreEvent($entityData, $moduleName)
	{
		$recordid = $entityData->getId();

		//To get the record model of the restored record
		$recordmodel = Vtiger_Record_Model::getInstanceById($recordid, $moduleName);

		$values['crmid'] = $recordid;
		$values['setype'] = $moduleName;
		$recordModel = new PBXManager_Record_Model;

		$moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		$fieldsModel = $moduleInstance->getFieldsByType('phone');

		foreach ($fieldsModel as $field => $fieldName) {
			$fieldName = $fieldName->get('name');
			$values[$fieldName] = $recordmodel->get($fieldName);

			if ($values[$fieldName])
				$recordModel->receivePhoneLookUpRecord($fieldName, $values, true);
		}
	}
}

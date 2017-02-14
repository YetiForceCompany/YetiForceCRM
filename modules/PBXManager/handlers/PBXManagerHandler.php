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
		(new PBXManager_Record_Model())->deletePhoneLookUpRecord($eventHandler->getRecordModel()->getId());
	}

	/**
	 * EntityAfterSave function
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
		$fields = $recordModel->getModule()->getFieldsByType('phone');
		foreach ($fields as $fieldName => &$fieldModel) {
			if (!$recordModel->isEmpty($fieldName)) {
				$values[$fieldName] = $recordModel->get($fieldName);
				$pbxRecordModel->receivePhoneLookUpRecord($fieldName, $values, true);
			}
		}
	}

	/**
	 * EntityAfterRestore handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterRestore(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$values = [
			'crmid' => $recordModel->getId(),
			'setype' => $eventHandler->getModuleName(),
		];
		$pbxRecordModel = new PBXManager_Record_Model;
		$fields = $recordModel->getModule()->getFieldsByType('phone');
		foreach ($fields as $fieldName => &$fieldModel) {
			if (!$recordModel->isEmpty($fieldName)) {
				$values[$fieldName] = $recordModel->get($fieldName);
				$pbxRecordModel->receivePhoneLookUpRecord($fieldName, $values, true);
			}
		}
	}
}

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

class Vtiger_MassSave_Action extends Vtiger_Mass_Action
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'Save')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'MassEdit')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$recordModels = $this->getRecordModelsFromRequest($request);
		$allRecordSave = true;
		foreach ($recordModels as $recordModel) {
			if ($recordModel !== false) {
				$recordModel->save();
			} else {
				$allRecordSave = false;
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($allRecordSave);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param \App\Request $request
	 * @return array - List of Vtiger_Record_Model instances
	 */
	public function getRecordModelsFromRequest(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordIds = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$recordModels = [];
		foreach ($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
			if (!$recordModel->isEditable()) {
				$recordModels[$recordId] = false;
				continue;
			}
			$fieldModelList = $moduleModel->getFields();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				if (!$fieldModel->isEditable()) {
					continue;
				}
				if ($request->has($fieldName)) {
					if ($fieldModel->get('uitype') === 300) {
						$recordModel->set($fieldName, $request->getForHtml($fieldName, null));
					} else {
						$recordModel->set($fieldName, $fieldModel->getUITypeModel()->getDBValue($request->get($fieldName, null), $recordModel));
					}
				}
			}
			$recordModels[$recordId] = $recordModel;
		}
		return $recordModels;
	}
}

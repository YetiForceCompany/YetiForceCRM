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

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'Save')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordModels = $this->getRecordModelsFromRequest($request);
		$allRecordSave = true;
		foreach ($recordModels as $recordId => &$recordModel) {
			if (Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)) {
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
	 * @param Vtiger_Request $request
	 * @return array - List of Vtiger_Record_Model instances
	 */
	public function getRecordModelsFromRequest(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordIds = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$recordModels = [];

		foreach ($recordIds as &$recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
			if (!$recordModel->isEditable()) {
				continue;
			}
			$fieldModelList = $moduleModel->getFields();
			foreach ($fieldModelList as $fieldName => &$fieldModel) {
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

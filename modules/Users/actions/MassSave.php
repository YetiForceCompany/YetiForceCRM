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

class Users_MassSave_Action extends Vtiger_MassSave_Action
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModels = $this->getRecordModelsFromRequest($request);
		foreach ($recordModels as $recordId => $recordModel) {
			if (\App\Privilege::isPermitted($moduleName, 'Save', $recordId)) {
				$recordModel->save();
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param \App\Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelsFromRequest(\App\Request $request)
	{

		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordIds = $this->getRecordsListFromRequest($request);

		if (empty($recordIds) && $request->get('selected_ids') == 'all') {
			$db = PearDatabase::getInstance();

			$sql = "SELECT `id` FROM `vtiger_users`";
			$result = $db->query($sql, true);
			$uNum = $db->numRows($result);

			if ($uNum > 0) {
				$recordIds = [];
				for ($i = 0; $i < $uNum; $i++) {
					$recordIds[] = $db->queryResult($result, $i, 'id');
				}
			}
		}
		$recordModels = [];

		$fieldModelList = $moduleModel->getFields();
		foreach ($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
			$recordModel->setId($recordId);

			foreach ($fieldModelList as $fieldName => $fieldModel) {
				$fieldValue = $request->get($fieldName, null);
				$fieldDataType = $fieldModel->getFieldDataType();
				if ($fieldDataType == 'time') {
					$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if (isset($fieldValue) && $fieldValue !== null) {
					if (!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				} else {
					$uiType = $fieldModel->get('uitype');
					if ($uiType == 70) {
						$recordModel->set($fieldName, $recordModel->get($fieldName));
					} else {
						$uiTypeModel = $fieldModel->getUITypeModel();
						$recordModel->set($fieldName, $uiTypeModel->getDBValue($recordModel->get($fieldName), $recordModel));
					}
				}
			}
			$recordModels[$recordId] = $recordModel;
		}
		return $recordModels;
	}
}

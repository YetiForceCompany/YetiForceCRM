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

class Documents_CheckFileIntegrity_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();

		if (!Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $request->get('record'))) {
			throw new \Exception\NoPermittedToRecord(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$documentRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$resultVal = $documentRecordModel->checkFileIntegrity();

		$result = array('success' => $resultVal);
		if ($resultVal) {
			$documentRecordModel->updateFileStatus(1);
			$result['message'] = App\Language::translate('LBL_FILE_AVAILABLE', $moduleName);
		} else {
			$documentRecordModel->updateFileStatus(0);
			$result['message'] = App\Language::translate('LBL_FILE_NOT_AVAILABLE', $moduleName);
		}
		$result['url'] = $documentRecordModel->getDetailViewUrl();
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

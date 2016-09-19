<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Settings_Webforms_Delete_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		parent::checkPermission($request);
		$recordId = $request->get('record');
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$recordId || !$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_Webforms_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		$moduleModel = $recordModel->getModule();

		$recordModel->delete();

		$returnUrl = $moduleModel->getListViewUrl();
		$response = new Vtiger_Response();
		$response->setResult($returnUrl);
		return $response;
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}

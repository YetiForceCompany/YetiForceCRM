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

class Rss_Save_Action extends Vtiger_Save_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule();
		$url = $request->get('feedurl');
		$recordModel = Rss_Record_Model::getCleanInstance($moduleName);
		$result = $recordModel->validateRssUrl($url);
		if ($result) {
			$recordModel->saveRecord($url);
			$response->setResult(['success' => true, 'message' => vtranslate('JS_RSS_SUCCESSFULLY_SAVED', $moduleName), 'id' => $recordModel->getId()]);
		} else {
			$response->setResult(['success' => false, 'message' => vtranslate('JS_INVALID_RSS_URL', $moduleName)]);
		}

		$response->emit();
	}
}

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

class Rss_Save_Action extends Vtiger_Save_Action
{
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(App\Request $request)
	{
		$response = new Vtiger_Response();
		$moduleName = $request->getModule();
		$url = $request->getByType('feedurl', \App\Purifier::URL);
		$recordModel = Rss_Record_Model::getCleanInstance($moduleName);
		$result = $recordModel->validateRssUrl($url);
		if ($result) {
			$recordModel->saveRecord($url);
			$response->setResult(['success' => true, 'message' => \App\Language::translate('JS_RSS_SUCCESSFULLY_SAVED', $moduleName), 'id' => $recordModel->getId()]);
		} else {
			$response->setResult(['success' => false, 'message' => \App\Language::translate('JS_INVALID_RSS_URL', $moduleName)]);
		}

		$response->emit();
	}
}

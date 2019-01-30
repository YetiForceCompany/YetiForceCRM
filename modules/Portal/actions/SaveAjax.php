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

class Portal_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$module = $request->getModule();
		$recordId = $request->isEmpty('record') ? null : $request->getInteger('record');
		$bookmarkName = $request->getByType('bookmarkName', 'Text');
		$bookmarkUrl = $request->getByType('bookmarkUrl', 'Text');

		Portal_Module_Model::savePortalRecord($recordId, $bookmarkName, $bookmarkUrl);

		$response = new Vtiger_Response();
		$result = ['message' => \App\Language::translate('LBL_BOOKMARK_SAVED_SUCCESSFULLY', $module)];
		$response->setResult($result);
		$response->emit();
	}
}

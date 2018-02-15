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

class Portal_MassDelete_Action extends Vtiger_Mass_Action
{
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$module = $request->getModule();

		Portal_Module_Model::deleteRecords($request);

		$response = new Vtiger_Response();
		$result = ['message' => \App\Language::translate('LBL_BOOKMARKS_DELETED_SUCCESSFULLY', $module)];
		$response->setResult($result);
		$response->emit();
	}
}

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

class CustomView_Approve_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!CustomView_Record_Model::getInstanceById($request->getInteger('record'))->isPending()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if ($currentUser->isAdminUser()) {
			$customViewModel = CustomView_Record_Model::getInstanceById($request->getInteger('record'));
			$moduleModel = $customViewModel->getModule();
			$customViewModel->approve();
		}
		$listViewUrl = $moduleModel->getListViewUrl();
		header("location: $listViewUrl");
	}
}

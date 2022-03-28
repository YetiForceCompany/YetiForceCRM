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

class Users_DeleteAjax_Action extends Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser() || $currentUserModel->getId() === $request->getInteger('userid')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$userId = $request->getInteger('userid');
		$transformUserId = $request->getInteger('transfer_user_id');
		if ('permanent' === $request->getMode()) {
			Users_Record_Model::deleteUserPermanently($userId, $transformUserId);
		} else {
			$userObj = new Users();
			$userObj->transformOwnerShipAndDelete($userId, $transformUserId);
			if ($request->getBoolean('permanent')) {
				Users_Record_Model::deleteUserPermanently($userId, $transformUserId);
			}
		}
		$userModuleModel = Users_Module_Model::getInstance($moduleName);
		$listViewUrl = $userModuleModel->getListViewUrl();
		$response = new Vtiger_Response();
		$response->setResult(['message' => \App\Language::translate('LBL_USER_DELETED_SUCCESSFULLY', $moduleName), 'listViewUrl' => $listViewUrl]);
		$response->emit();
	}
}

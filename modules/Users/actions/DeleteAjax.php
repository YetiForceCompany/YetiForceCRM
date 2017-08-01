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
Vtiger_Loader::includeOnce('~include/Webservices/Custom/DeleteUser.php');

class Users_DeleteAjax_Action extends Vtiger_Delete_Action
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$ownerId = $request->get('userid');
		$newOwnerId = $request->get('transfer_user_id');
		if ($request->get('mode') === 'permanent') {
			Users_Record_Model::deleteUserPermanently($ownerId, $newOwnerId);
		} else {
			$userId = vtws_getWebserviceEntityId($moduleName, $ownerId);
			$transformUserId = vtws_getWebserviceEntityId($moduleName, $newOwnerId);

			$userModel = Users_Record_Model::getCurrentUserModel();

			vtws_deleteUser($userId, $transformUserId, $userModel);

			if ($request->get('permanent') === '1')
				Users_Record_Model::deleteUserPermanently($ownerId, $newOwnerId);
		}
		$userModuleModel = Users_Module_Model::getInstance($moduleName);
		$listViewUrl = $userModuleModel->getListViewUrl();
		$response = new Vtiger_Response();
		$response->setResult(['message' => \App\Language::translate('LBL_USER_DELETED_SUCCESSFULLY', $moduleName), 'listViewUrl' => $listViewUrl]);
		$response->emit();
	}
}

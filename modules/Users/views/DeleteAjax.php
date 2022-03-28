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

class Users_DeleteAjax_View extends Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			return true;
		}
		throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$userid = $request->getInteger('record');

		$userRecordModel = Users_Record_Model::getInstanceById($userid, $moduleName);
		$viewer = $this->getViewer($request);
		$usersList = $userRecordModel->getAll(true);

		if (\array_key_exists($userid, $usersList)) {
			unset($usersList[$userid]);
		}

		$viewer->assign('USERID', $userid);
		$viewer->assign('DELETE_USER_NAME', $userRecordModel->getName());
		$viewer->assign('USER_LIST', $usersList);
		$viewer->view('DeleteUser.tpl', $moduleName);
	}
}

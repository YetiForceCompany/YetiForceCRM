<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Users_TransferOwner_View extends Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$userid = $request->getInteger('record');
		$userRecordModel = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$usersList = $userRecordModel->getActiveAdminUsers(true);
		if (\array_key_exists($userid, $usersList)) {
			unset($usersList[$userid]);
		}

		$viewer->assign('USERID', $userid);
		$viewer->assign('TRANSFER_USER_NAME', $userRecordModel->getName());
		$viewer->assign('USER_LIST', $usersList);
		$viewer->view('TransferOwner.tpl', $moduleName);
	}
}

<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

Class Users_EditAjax_View extends Vtiger_IndexAjax_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');
		if ($currentUserModel->isAdminUser() === true || $currentUserModel->get('id') == $record) {
			return true;
		} else {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('changePassword');
		$this->exposeMethod('editPasswords');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function changePassword(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->get('module');
		$userId = $request->get('record');

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USERID', $userId);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('ChangePassword.tpl', $moduleName);
	}

	public function editPasswords(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->get('module');
		$userIds = $request->get('userids');

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USERIDS', json_encode($userIds));
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('EditPasswords.tpl', $moduleName);
	}
}

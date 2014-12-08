<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Users_DeleteAjax_View extends Vtiger_Index_View {
	
	public function checkPermission(Vtiger_Request $request){
		parent::checkPermission($request);
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$userid = $request->get('record');
		
		$userRecordModel = Users_Record_Model::getInstanceById($userid, $moduleName);
		$viewer = $this->getViewer($request);
		$usersList = $userRecordModel->getAll(true);
		
		if(array_key_exists($userid, $usersList)){
			unset($usersList[$userid]);
		}
		
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USERID', $userid);
		$viewer->assign('DELETE_USER_NAME', $userRecordModel->getName());
		$viewer->assign('USER_LIST', $usersList);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		
		$viewer->view('DeleteUser.tpl', $moduleName);
	}
}

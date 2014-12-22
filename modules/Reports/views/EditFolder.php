<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Reports_EditFolder_View extends Vtiger_IndexAjax_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process (Vtiger_Request $request) {
		
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$folderId = $request->get('folderid');

		if ($folderId) {
			$folderModel = Reports_Folder_Model::getInstanceById($folderId);
		} else {
			$folderModel = Reports_Folder_Model::getInstance();
		}
		
		$viewer->assign('FOLDER_MODEL', $folderModel);
		$viewer->assign('MODULE',$moduleName);
		$viewer->view('EditFolder.tpl', $moduleName);
	}
}
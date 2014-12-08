<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_ChangePassword_View extends Vtiger_Basic_View {
    
    public function preProcess (Vtiger_Request $request, $display=true) {
		parent::preProcess($request, false);
        
		$viewer = $this->getViewer($request);

		$moduleName = $request->getModule();
		if(!empty($moduleName)) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
			$viewer->assign('MODULE', $moduleName);

			//Dont check for module permissions since for non admin users module permission will not be there 

			$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
			$linkModels = $moduleModel->getSideBarLinks($linkParams);

			$viewer->assign('QUICK_LINKS', $linkModels);
		}
		
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('CURRENT_VIEW', $request->get('view'));
		if($display) {
			$this->preProcessDisplay($request);
		}
	}
    
    
    protected function preProcessDisplay(Vtiger_Request $request) {}
	
	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('UI5_URL', $this->getUI5EmbedURL($request));
		$viewer->view('UI5EmbedView.tpl');
	}
    
}
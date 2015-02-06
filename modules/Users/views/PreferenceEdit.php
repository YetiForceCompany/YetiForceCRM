<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Users_PreferenceEdit_View extends Vtiger_Edit_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');
		if (!empty($record) && $currentUserModel->get('id') != $record) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			if($recordModel->get('status') != 'Active') {
				throw new AppException('LBL_PERMISSION_DENIED');
			}
		}
		if(($currentUserModel->isAdminUser() == true || $currentUserModel->get('id') == $record)) {
			return true;
		} else {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	function preProcessTplName(Vtiger_Request $request) {
		return 'UserEditViewPreProcess.tpl';
	}


	public function preProcess (Vtiger_Request $request, $display=true) {
		if($this->checkPermission($request)) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$viewer = $this->getViewer($request);
			$menuModelsList = Vtiger_Menu_Model::getAll(true);
			$selectedModule = $request->getModule();
			$menuStructure = Vtiger_MenuStructure_Model::getInstanceFromMenuList($menuModelsList, $selectedModule);

			// Order by pre-defined automation process for QuickCreate.
			uksort($menuModelsList, array('Vtiger_MenuStructure_Model', 'sortMenuItemsByProcess'));

			$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
			$companyLogo = $companyDetails->getLogo();

			$viewer->assign('CURRENTDATE', date('Y-n-j'));
			$viewer->assign('MODULE', $selectedModule);
			$viewer->assign('PARENT_MODULE', $request->get('parent'));
            $viewer->assign('VIEW', $request->get('view'));
			$viewer->assign('MENUS', $menuModelsList);
			$viewer->assign('MENU_STRUCTURE', $menuStructure);
			$viewer->assign('MENU_SELECTED_MODULENAME', $selectedModule);
			$viewer->assign('MENU_TOPITEMS_LIMIT', $menuStructure->getLimit());
			$viewer->assign('COMPANY_LOGO',$companyLogo);
			$viewer->assign('USER_MODEL', $currentUser);
            $viewer->assign('SEARCHABLE_MODULES', Vtiger_Module_Model::getSearchableModules());

			$homeModuleModel = Vtiger_Module_Model::getInstance('Home');
			$viewer->assign('HOME_MODULE_MODEL', $homeModuleModel);
			$viewer->assign('HEADER_LINKS',$this->getHeaderLinks());
			$viewer->assign('ANNOUNCEMENT', $this->getAnnouncement());

			$viewer->assign('CURRENT_VIEW', $request->get('view'));
			$viewer->assign('PAGETITLE', $this->getPageTitle($request));
			$viewer->assign('SCRIPTS',$this->getHeaderScripts($request));
			$viewer->assign('STYLES',$this->getHeaderCss($request));
			$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
			$viewer->assign('SKIN_PATH', Vtiger_Theme::getCurrentUserThemePath());
			$viewer->assign('IS_PREFERENCE', true);
			$viewer->assign('LANGUAGE', $currentUser->get('language'));
			
			$allUsers = Users_Record_Model::getAll(true);
			$sharedUsers = Calendar_Module_Model::getCaledarSharedUsers($currentUser->id);
			$sharedType = Calendar_Module_Model::getSharedType($currentUser->id);
			$viewer->assign('ALL_USERS',$allUsers);
			$viewer->assign('SHAREDUSERS',$sharedUsers);
			$viewer->assign('SHARED_TYPE',$sharedType);
			// OpenSaaS
			$OSSMenu = OSSMenuManager_Record_Model::getMenu();
			$viewer->assign('MENU_STRUCTURE', $OSSMenu);
			//  OpenSaaS
			
			if($display) {
				$this->preProcessDisplay($request);
			}
		}
	}

	protected function preProcessDisplay(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->view($this->preProcessTplName($request), $request->getModule());
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		}

		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$dayStartPicklistValues = Users_Record_Model::getDayStartsPicklistValues($recordStructureInstance->getStructure());

		$viewer = $this->getViewer($request);
		$viewer->assign("DAY_STARTS", Zend_Json::encode($dayStartPicklistValues));
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		parent::process($request);
	}

    public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();
        $moduleDetailFile = 'modules.'.$moduleName.'.resources.PreferenceEdit';
        unset($headerScriptInstances[$moduleDetailFile]);

		$jsFileNames = array(
            "modules.Users.resources.Edit",
            'modules.'.$moduleName.'.resources.PreferenceEdit'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
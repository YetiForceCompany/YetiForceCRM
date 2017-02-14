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

Class Users_PreferenceEdit_View extends Vtiger_Edit_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');
		if (!AppConfig::security('SHOW_MY_PREFERENCES')) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
		if (!empty($record) && $currentUserModel->get('id') != $record) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			if ($recordModel->get('status') != 'Active') {
				throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
		}
		if (($currentUserModel->isAdminUser() === true || $currentUserModel->get('id') == $record)) {
			return true;
		} else {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcessTplName(Vtiger_Request $request)
	{
		return 'UserEditViewPreProcess.tpl';
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		if ($this->checkPermission($request)) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$viewer = $this->getViewer($request);
			if ($activeReminder = \App\Module::isModuleActive('Calendar')) {
				$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
				$activeReminder = $userPrivilegesModel->hasModulePermission('Calendar');
			}
			$selectedModule = $request->getModule();
			$currentDate = Vtiger_Date_UIType::getDisplayDateValue(date('Y-n-j'));
			$viewer->assign('CURRENTDATE', $currentDate);
			$viewer->assign('MODULE', $selectedModule);
			$viewer->assign('MODULE_NAME', $selectedModule);
			$viewer->assign('QUALIFIED_MODULE', $selectedModule);
			$viewer->assign('PARENT_MODULE', $request->get('parent'));
			$viewer->assign('MENUS', Vtiger_Menu_Model::getAll(true));
			$viewer->assign('VIEW', $request->get('view'));
			$viewer->assign('USER_MODEL', $currentUser);

			$homeModuleModel = Vtiger_Module_Model::getInstance('Home');
			$viewer->assign('HOME_MODULE_MODEL', $homeModuleModel);
			$viewer->assign('MENU_HEADER_LINKS', $this->getMenuHeaderLinks($request));
			$viewer->assign('SEARCHABLE_MODULES', Vtiger_Module_Model::getSearchableModules());
			$viewer->assign('CHAT_ACTIVE', \App\Module::isModuleActive('AJAXChat'));
			$viewer->assign('REMINDER_ACTIVE', $activeReminder);
			$viewer->assign('SHOW_BODY_HEADER', $this->showBodyHeader());
			//Additional parameters
			$viewer->assign('CURRENT_VIEW', $request->get('view'));
			$viewer->assign('PAGETITLE', $this->getPageTitle($request));
			$viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
			$viewer->assign('STYLES', $this->getHeaderCss($request));
			$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
			$viewer->assign('SKIN_PATH', Vtiger_Theme::getCurrentUserThemePath());
			$viewer->assign('IS_PREFERENCE', true);
			$viewer->assign('HTMLLANG', Vtiger_Language_Handler::getShortLanguageName());
			$viewer->assign('LANGUAGE', $currentUser->get('language'));
			$viewer->assign('HEADER_SCRIPTS', $this->getHeaderScripts($request));
			if ($display) {
				$this->preProcessDisplay($request);
			}
		}
	}

	protected function preProcessDisplay(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view($this->preProcessTplName($request), $request->getModule());
	}

	public function process(Vtiger_Request $request)
	{
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
		$viewer->assign("DAY_STARTS", \App\Json::encode($dayStartPicklistValues));
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		parent::process($request);
	}
}

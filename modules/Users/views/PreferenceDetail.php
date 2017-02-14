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

class Users_PreferenceDetail_View extends Vtiger_Detail_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');

		if (!AppConfig::security('SHOW_MY_PREFERENCES')) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
		if ($currentUserModel->isAdminUser() === true || $currentUserModel->get('id') == $record) {
			return true;
		} else {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function to returns the preProcess Template Name
	 * @param <type> $request
	 * @return string
	 */
	public function preProcessTplName(Vtiger_Request $request)
	{
		return 'PreferenceDetailViewPreProcess.tpl';
	}

	/**
	 * Function shows basic detail for the record
	 * @param <type> $request
	 */
	public function showModuleBasicView(Vtiger_Request $request)
	{
		return $this->showModuleDetailView($request);
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		if ($this->checkPermission($request)) {
			$viewer = $this->getViewer($request);
			if ($activeReminder = \App\Module::isModuleActive('Calendar')) {
				$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
				$activeReminder = $userPrivilegesModel->hasModulePermission('Calendar');
			}
			$currentUser = Users_Record_Model::getCurrentUserModel();
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
			$recordId = $request->get('record');
			$moduleName = $request->getModule();
			$detailViewModel = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
			$recordModel = $detailViewModel->getRecord();
			$detailViewLinkParams = array('MODULE' => $moduleName, 'RECORD' => $recordId);
			$detailViewLinks = $detailViewModel->getDetailViewLinks($detailViewLinkParams);
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('MODULE_MODEL', $detailViewModel->getModule());
			$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
			$viewer->assign('IS_EDITABLE', $detailViewModel->getRecord()->isEditable($moduleName));
			$viewer->assign('IS_DELETABLE', $detailViewModel->getRecord()->isDeletable($moduleName));

			$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
			$linkModels = $detailViewModel->getSideBarLinks($linkParams);
			$viewer->assign('QUICK_LINKS', $linkModels);
			$viewer->assign('PAGETITLE', $this->getPageTitle($request));
			$viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
			$viewer->assign('STYLES', $this->getHeaderCss($request));
			$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
			$viewer->assign('CURRENT_VIEW', $request->get('view'));
			$viewer->assign('SKIN_PATH', Vtiger_Theme::getCurrentUserThemePath());
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
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$dayStartPicklistValues = Users_Record_Model::getDayStartsPicklistValues($recordStructureInstance->getStructure());
		$viewer = $this->getViewer($request);
		$viewer->assign('DAY_STARTS', \App\Json::encode($dayStartPicklistValues));
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::process($request);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$moduleDetailFile = 'modules.' . $moduleName . '.resources.PreferenceDetail';
		unset($headerScriptInstances[$moduleDetailFile]);

		$jsFileNames = array(
			'modules.Vtiger.resources.Detail',
			'modules.Users.resources.Detail',
			'modules.' . $moduleName . '.resources.PreferenceDetail',
			'modules.' . $moduleName . '.resources.PreferenceEdit'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}

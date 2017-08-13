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

	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!AppConfig::security('SHOW_MY_PREFERENCES')) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
		if ($currentUserModel->isAdminUser() === true || (int) $currentUserModel->get('id') === $request->getInteger('record')) {
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
	public function preProcessTplName(\App\Request $request)
	{
		return 'PreferenceDetailViewPreProcess.tpl';
	}

	/**
	 * Function shows basic detail for the record
	 * @param <type> $request
	 */
	public function showModuleBasicView(\App\Request $request)
	{
		return $this->showModuleDetailView($request);
	}

	protected function preProcessDisplay(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view($this->preProcessTplName($request), $request->getModule());
	}

	public function process(\App\Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$dayStartPicklistValues = $recordModel->getDayStartsPicklistValues();
		$viewer = $this->getViewer($request);
		$viewer->assign('DAY_STARTS', \App\Json::encode($dayStartPicklistValues));
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());
		return parent::process($request);
	}

	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$moduleDetailFile = 'modules.' . $moduleName . '.resources.PreferenceDetail';
		unset($headerScriptInstances[$moduleDetailFile]);
		$jsFileNames = [
			'modules.Vtiger.resources.Detail',
			'modules.Users.resources.Detail',
			'modules.' . $moduleName . '.resources.PreferenceDetail',
			'modules.' . $moduleName . '.resources.PreferenceEdit'
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return array_merge($headerScriptInstances, $jsScriptInstances);
	}
}

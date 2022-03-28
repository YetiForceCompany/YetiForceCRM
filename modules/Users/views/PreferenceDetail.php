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

class Users_PreferenceDetail_View extends Users_Detail_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!App\Config::security('SHOW_MY_PREFERENCES')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (true === $currentUserModel->isAdminUser() || (int) $currentUserModel->get('id') === $request->getInteger('record')) {
			return true;
		}
		throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
	}

	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		$this->record = Vtiger_DetailView_Model::getInstance($request->getModule(), $request->getInteger('record'));
		parent::preProcess($request, $display);
	}

	/** {@inheritdoc} */
	public function preProcessTplName(App\Request $request)
	{
		return 'PreferenceDetailViewPreProcess.tpl';
	}

	/** {@inheritdoc} */
	public function showModuleBasicView(App\Request $request)
	{
		return $this->showModuleDetailView($request);
	}

	/** {@inheritdoc} */
	protected function preProcessDisplay(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view($this->preProcessTplName($request), $request->getModule());
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$moduleDetailFile = 'modules.' . $moduleName . '.resources.PreferenceDetail';
		unset($headerScriptInstances[$moduleDetailFile]);
		$jsFileNames = [
			'modules.Vtiger.resources.Detail',
			'modules.Users.resources.Detail',
			'modules.' . $moduleName . '.resources.PreferenceDetail',
			'modules.' . $moduleName . '.resources.PreferenceEdit',
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

		return array_merge($headerScriptInstances, $jsScriptInstances);
	}
}

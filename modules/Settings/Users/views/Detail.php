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

class Settings_Users_Detail_View extends Users_PreferenceDetail_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (\App\Security\AdminAccess::isPermitted($request->getModule()) || (\App\User::getCurrentUserId() === $request->getInteger('record') && App\Config::security('SHOW_MY_PREFERENCES'))) {
			return true;
		}
		throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
	}

	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$this->preProcessSettings($request);
	}

	/**
	 * Pre process settings.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessSettings(App\Request $request)
	{
		if (!empty(\Config\Security::$askSuperUserAboutVisitPurpose) && !\App\Session::has('showedModalVisitPurpose') && !\App\User::getCurrentUserModel()->isAdmin()) {
			\App\Process::addEvent([
				'name' => 'showSuperUserVisitPurpose',
				'type' => 'modal',
				'url' => 'index.php?module=Users&view=VisitPurpose'
			]);
		}
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$view = $request->getByType('view', \App\Purifier::STANDARD, '');
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('MENUS', Settings_Vtiger_Menu_Model::getMenu($moduleName, $view, $request->getMode()));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('SettingsMenuStart.tpl', $qualifiedModuleName);
	}

	public function postProcessSettings(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->view('SettingsMenuEnd.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function postProcess(App\Request $request, $display = true)
	{
		$this->postProcessSettings($request);
		parent::postProcess($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('UserViewHeader.tpl', $request->getModule());
		parent::process($request);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.Vtiger.resources.Index',
		]));
	}

	/**
	 * Function to get Ajax is enabled or not.
	 *
	 * @param Vtiger_Record_Model record model
	 * @param mixed $recordModel
	 *
	 * @return bool
	 */
	public function isAjaxEnabled($recordModel)
	{
		if ('Active' != $recordModel->get('status')) {
			return false;
		}
		return $recordModel->isEditable();
	}
}

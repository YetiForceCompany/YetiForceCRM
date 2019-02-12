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

class Users_PreferenceEdit_View extends Vtiger_Edit_View
{
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!AppConfig::security('SHOW_MY_PREFERENCES')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!$request->isEmpty('record', true)) {
			$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			if ($currentUserModel->get('id') != $request->getInteger('record') && $this->record->get('status') != 'Active') {
				throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
			}
		} elseif ($request->isEmpty('record')) {
			$this->record = Vtiger_Record_Model::getCleanInstance($moduleName);
		}
		if (($currentUserModel->isAdminUser() === true || ($currentUserModel->get('id') == $request->getInteger('record') && AppConfig::security('SHOW_MY_PREFERENCES')))) {
			return true;
		} else {
			throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcessTplName(\App\Request $request)
	{
		return 'UserEditViewPreProcess.tpl';
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$viewer->assign('IS_PREFERENCE', true);
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function preProcessDisplay(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view($this->preProcessTplName($request), $request->getModule());
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!$request->isEmpty('record')) {
			$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		}
		$dayStartPicklistValues = $recordModel->getDayStartsPicklistValues();
		$viewer = $this->getViewer($request);
		$viewer->assign('DAY_STARTS', \App\Json::encode($dayStartPicklistValues));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		parent::process($request);
	}
}

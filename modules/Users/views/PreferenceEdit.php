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

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$record = $request->getInteger('record');
		if (!AppConfig::security('SHOW_MY_PREFERENCES')) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
		if ($record && (int) $currentUserModel->get('id') !== $record) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			if ($recordModel->get('status') !== 'Active') {
				throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
		}
		if (($currentUserModel->isAdminUser() === true || (int) $currentUserModel->get('id') === $record)) {
			return true;
		} else {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcessTplName(\App\Request $request)
	{
		return 'UserEditViewPreProcess.tpl';
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		if ($this->checkPermission($request)) {
			$viewer = $this->getViewer($request);
			$viewer->assign('IS_PREFERENCE', true);
			if ($display) {
				$this->preProcessDisplay($request);
			}
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
		$recordId = $request->get('record');
		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		}
		$dayStartPicklistValues = $recordModel->getDayStartsPicklistValues();
		$viewer = $this->getViewer($request);
		$viewer->assign("DAY_STARTS", \App\Json::encode($dayStartPicklistValues));
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		parent::process($request);
	}
}

<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_Index_View extends Vtiger_Basic_View
{

	public function __construct()
	{
		parent::__construct();
	}

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!empty($moduleName)) {
			$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			$permission = $userPrivilegesModel->hasModulePermission($moduleName);

			if (!$permission) {
				throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
			}
		}
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if (!empty($moduleName)) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
			$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
			if (!$permission) {
				throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
			}

			$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
			$linkModels = $moduleModel->getSideBarLinks($linkParams);

			$viewer->assign('QUICK_LINKS', $linkModels);
		}
		$viewer->assign('CURRENT_VIEW', $request->get('view'));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function preProcessTplName(\App\Request $request)
	{
		return 'IndexViewPreProcess.tpl';
	}

	//Note : To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/* function preProcessParentTplName(\App\Request $request) {
	  return parent::preProcessTplName($request);
	  } */

	public function postProcess(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('IndexPostProcess.tpl', $moduleName);

		parent::postProcess($request);
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('Index.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param \App\Request $request
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$view = $request->get('view');
		$jsFileNames = array(
			'modules.Vtiger.resources.Vtiger',
			'modules.Vtiger.resources.' . $view,
			"modules.$moduleName.resources.$moduleName",
			"modules.$moduleName.resources.$view",
			'libraries.jquery.ckeditor.ckeditor',
			'libraries.jquery.ckeditor.adapters.jquery',
			'modules.Vtiger.resources.CkEditor',
		);
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateReadAccess();
	}
}

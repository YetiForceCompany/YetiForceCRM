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

	public function checkPermission(Vtiger_Request $request)
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

	public function preProcess(Vtiger_Request $request, $display = true)
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

	protected function preProcessTplName(Vtiger_Request $request)
	{
		return 'IndexViewPreProcess.tpl';
	}

	//Note : To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/* function preProcessParentTplName(Vtiger_Request $request) {
	  return parent::preProcessTplName($request);
	  } */

	public function postProcess(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('IndexPostProcess.tpl', $moduleName);

		parent::postProcess($request);
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('Index.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
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

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateReadAccess();
	}
}

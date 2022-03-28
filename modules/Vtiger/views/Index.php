<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Vtiger_Index_View extends \App\Controller\View\Page
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if (!empty($moduleName)) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->getByType('view', 1)];
			$linkModels = $moduleModel->getSideBarLinks($linkParams);
			$viewer->assign('QUICK_LINKS', $linkModels);
		}
		$viewer->assign('CURRENT_VIEW', $request->getByType('view', 1));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	/** {@inheritdoc} */
	protected function preProcessTplName(App\Request $request)
	{
		return 'IndexViewPreProcess.tpl';
	}

	/** {@inheritdoc} */
	public function postProcess(App\Request $request, $display = true)
	{
		parent::postProcess($request, $display);
		$this->getViewer($request)->view('IndexPostProcess.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->getViewer($request)->view('Index.tpl', $request->getModule());
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$view = $request->getByType('view', 1);
		$jsFileNames = [
			'modules.Vtiger.resources.Vtiger',
			'modules.Vtiger.resources.' . $view,
			"modules.$moduleName.resources.$moduleName",
			"modules.$moduleName.resources.$view",
		];

		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}
}

<?php

/**
 * Settings ModuleManager CreateModule view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_ModuleManager_CreateModule_View extends Settings_Vtiger_Index_View
{
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('CreateModule.tpl', $qualifiedModuleName);
	}
}

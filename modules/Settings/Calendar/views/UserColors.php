<?php

/**
 * Settings calendar UserColors view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Calendar_UserColors_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Calendar_Module_Model::getInstance($qualifiedModuleName);
		$notWorkDays = Settings_Calendar_Module_Model::getNotWorkingDays();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('NOTWORKINGDAYS', $notWorkDays);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('UserColors.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = [
			"modules.Settings.$moduleName.resources.UserColors",
		];

		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}
}

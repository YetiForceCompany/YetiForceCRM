<?php

/**
 * Settings calendar UserColors view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Calendar_UserColors_View extends Settings_Vtiger_Index_View
{
	public function process(\App\Request $request)
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

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = [
			"modules.Settings.$moduleName.resources.UserColors",
			'~libraries/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js',
		];

		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
				'~libraries/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.css',
		]));
	}
}

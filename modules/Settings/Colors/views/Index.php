<?php

/**
 * Settings colors index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Sławomir Kłos <s.klos@yetiforce.com>
 */
class Settings_Colors_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Calendar_Module_Model::getInstance($qualifiedModuleName);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/**
	 * Get header css.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$cssFileNames = [
			'~libraries/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.css',
		];

		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles($cssFileNames));
	}

	/**
	 * Get footer scripts.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$jsFileNames = [
			'modules.Settings.' . $request->getModule() . '.resources.Colors',
			'~libraries/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js',
		];

		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}
}

<?php

/**
 * Settings Credits View class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Vtiger_Credits_View extends Settings_Vtiger_Index_View
{
	/**
	 * Function process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('LIBRARIES', \App\Installer\Credits::getCredits());
		$viewer->view('Credits.tpl', $qualifiedModuleName);
	}

	/**
	 * Function return footer scripts.
	 *
	 * @param \App\Request $request
	 *
	 * @return array|\Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.Settings.{$request->getModule()}.resources.Credits",
		]));
	}
}

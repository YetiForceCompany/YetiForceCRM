<?php

/**
 * Settings mail autologin view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Mail_Autologin_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', Settings_Mail_Autologin_Model::getInstance());
		$viewer->assign('ERROR_MESSAGE', $request->getByType('errorMessage', 'Text'));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Autologin.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return array - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.' . $request->getModule() . '.resources.Autologin',
		]));
	}
}

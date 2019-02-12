<?php

/**
 * Settings Password index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Password_Index_View extends Settings_Vtiger_Index_View
{
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('DETAIL', Settings_Password_Record_Model::getUserPassConfig());
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	public function getFooterScripts(\App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.' . $request->getModule() . '.resources.Password',
		]));
	}
}

<?php

/**
 * Settings dav keys view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Dav_Keys_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Dav_Module_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('USERS', Users_Record_Model::getAll());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ENABLEDAV', !in_array('dav', App\Config::api('enabledServices')));
		$viewer->view('Keys.tpl', $qualifiedModuleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
				'libraries.clipboard.dist.clipboard'
		]));
	}
}

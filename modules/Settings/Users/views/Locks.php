<?php

/**
 * Locks View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Users_Locks_View extends Settings_Vtiger_Index_View
{
	/**
	 * Page title.
	 *
	 * @var type
	 */
	protected $pageTitle = 'LBL_LOCKS';

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Users_Module_Model::getInstance();
		$viewer = $this->getViewer($request);
		$viewer->assign('LOCKS', $moduleModel->getLocks());
		$viewer->assign('LOCKS_TYPE', $moduleModel->getLocksTypes());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Locks.tpl', $qualifiedModuleName);
	}

	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$jsFileNames = [
			"modules.Settings.$moduleName.resources.Locks",
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}
}

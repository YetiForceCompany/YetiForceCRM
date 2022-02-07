<?php

/**
 * Locks View Class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Users_Locks_View extends Settings_Vtiger_Index_View
{
	/**
	 * Page title.
	 *
	 * @var type
	 */
	protected $pageTitle = 'LBL_LOCKS';

	public function process(App\Request $request)
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

	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.Settings.{$request->getModule()}.resources.Locks",
		]));
	}
}

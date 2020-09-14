<?php

/**
 * Settings admin access index view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings admin access index view class.
 */
class Settings_AdminAccess_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('permissions');
		$this->exposeMethod('historyAdminsVisitPurpose');
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		// $activeTab = 'Config';
		// if ($request->has('tab')) {
		// 	$activeTab = $request->getByType('tab', \App\Purifier::STANDARD);
		// }
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		// $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		// $viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('LINKS', $moduleModel->getLinks());
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function permissions(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		// $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		// $viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('LINKS', $moduleModel->getLinks());
		$viewer->view(\App\Utils::mbUcfirst($request->getMode()) . '.tpl', $qualifiedModuleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function historyAdminsVisitPurpose(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		// $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		// $viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('STRUCTURE', $moduleModel->getStructure('visitPurpose'));
		$viewer->assign('LINKS', $moduleModel->getLinks());
		$viewer->view(\App\Utils::mbUcfirst($request->getMode()) . '.tpl', $qualifiedModuleName);
	}
}

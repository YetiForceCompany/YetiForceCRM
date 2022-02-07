<?php

/**
 * Settings admin access index view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings admin access index view class.
 */
class Settings_AdminAccess_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('permissions');
		$this->exposeMethod('historyAdminsVisitPurpose');
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('TAB', $request->has('tab') ? $request->getByType('tab') : 'permissions');
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/**
	 * Gets permissions tab view.
	 *
	 * @param App\Request $request
	 */
	public function permissions(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('LINKS', $moduleModel->getLinks());
		$viewer->view(\App\Utils::mbUcfirst($request->getMode()) . '.tpl', $qualifiedModuleName);
	}

	/**
	 * Gets history admins visit purpose tab view.
	 *
	 * @param App\Request $request
	 */
	public function historyAdminsVisitPurpose(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('STRUCTURE', $moduleModel->getStructure('visitPurpose'));
		$viewer->assign('CONFIG_FIELDS', Settings_AdminAccess_Module_Model::getFields($qualifiedModuleName));
		$viewer->assign('CONFIG', App\Config::security());
		$viewer->view(\App\Utils::mbUcfirst($request->getMode()) . '.tpl', $qualifiedModuleName);
	}
}

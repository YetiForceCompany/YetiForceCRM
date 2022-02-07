<?php

/**
 * Configuration notifications.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Notifications_Configuration_View extends Settings_Vtiger_Index_View
{
	/**
	 * Function gets module settings.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$modules = Vtiger_Watchdog_Model::getSupportedModules();
		if ($request->isEmpty('srcModule')) {
			reset($modules);
			$srcModule = key($modules);
		} else {
			$srcModule = $request->getInteger('srcModule');
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('WATCHDOG_MODULE', Vtiger_Watchdog_Model::getInstance($srcModule));
		$viewer->assign('SELECTED_MODULE', $srcModule);
		$viewer->assign('SUPPORTED_MODULES', $modules);
		$viewer->view('Configuration.tpl', $request->getModule(false));
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
			'modules.Settings.' . $request->getModule() . '.resources.Configuration',
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
			'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
			'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js'
		]));
	}
}

<?php

/**
 * Form to add widget.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WidgetsManagement_AddRss_View extends Settings_Vtiger_BasicModal_View
{
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule(false);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->view('AddRss.tpl', $moduleName);
	}
}

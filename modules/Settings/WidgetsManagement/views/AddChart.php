<?php

/**
 * Form to add widget.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WidgetsManagement_AddChart_View extends Settings_Vtiger_BasicModal_View
{
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule(false);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->assign('LIST_REPORTS', Settings_WidgetsManagement_Module_Model::getReports());
		$viewer->view('AddChart.tpl', $moduleName);
	}
}

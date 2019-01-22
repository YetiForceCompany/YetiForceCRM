<?php

/**
 * FilterPermissions View Class for CustomView.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_FilterPermissions_View extends Settings_Vtiger_BasicModal_View
{
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$sourceModuleId = $request->getInteger('sourceModule');
		$moduleModel = Settings_LangManagement_Module_Model::getInstance($moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('IS_DEFAULT', $request->getBoolean('isDefault'));
		$viewer->assign('TYPE', $request->getByType('type', 'Standard'));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('SOURCE_MODULE', $sourceModuleId);
		$viewer->assign('CVID', $request->getInteger('cvid'));
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$this->preProcess($request);
		$viewer->view('FilterPermissions.tpl', $moduleName);
		$this->postProcess($request);
	}
}

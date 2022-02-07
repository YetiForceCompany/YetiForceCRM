<?php

/**
 * Settings GlobalPermission index view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_GlobalPermission_Index_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$globalPermissions = Settings_GlobalPermission_Record_Model::getGlobalPermissions();
		$viewer = $this->getViewer($request);
		$viewer->assign('GLOBALPERMISSIONS', $globalPermissions);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}

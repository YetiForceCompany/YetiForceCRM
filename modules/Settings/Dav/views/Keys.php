<?php

/**
 * Settings dav keys view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_Dav_Keys_View extends Settings_Vtiger_Index_View
{

	public function process(\App\Request $request)
	{
		include 'config/api.php';
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Dav_Module_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('USERS', Users_Record_Model::getAll());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ENABLEDAV', !in_array('dav', $enabledServices));
		$viewer->view('Keys.tpl', $qualifiedModuleName);
	}
}

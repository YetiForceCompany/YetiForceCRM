<?php

/**
 * Settings DataAccess ActionConfig view  class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
Class Settings_DataAccess_ActionConfig_View extends Settings_Vtiger_Index_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();
		$baseModule = $request->get('m');
		$tplId = $request->get('did');
		$aid = $request->get('aid');
		$action = $request->get('an');
		$actionsName = explode(Settings_DataAccess_Module_Model::$separator, $action);
		$Config = Settings_DataAccess_Module_Model::showConfigDataAccess($tplId, $action, $baseModule);
		$DataAccess = Settings_DataAccess_Module_Model::getDataAccessInfo($tplId, false);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('BASE_MODULE', $baseModule);
		$viewer->assign('ACTIONMOD', $actionsName[0]);
		$viewer->assign('ACTION', $actionsName[1]);
		$viewer->assign('ACTIONNAME', $action);
		$viewer->assign('AID', $aid);
		$viewer->assign('TPL_ID', $tplId);
		$viewer->assign('CONFIG', $Config);
		$viewer->assign('SAVED_DATA', $DataAccess['basic_info']['data'][$aid]);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		echo $viewer->view('ActionConfig.tpl', $qualifiedModuleName, true);
	}
}

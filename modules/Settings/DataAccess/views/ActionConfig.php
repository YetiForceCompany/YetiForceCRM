<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

Class Settings_DataAccess_ActionConfig_View extends Settings_Vtiger_Index_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(Vtiger_Request $request)
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

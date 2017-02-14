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

Class Settings_DataAccess_Step3_View extends Settings_Vtiger_Index_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(Vtiger_Request $request)
	{
		$db = \App\Db::getInstance();
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();
		$baseModule = $request->get('base_module');
		$tplId = $request->get('tpl_id');

		if ($request->get('s') == '' || $request->get('s') == 'true') {
			$summary = $request->get('summary');
			$conditionAll = $request->getRaw('condition_all_json');
			$conditionOption = $request->getRaw('condition_option_json');
			if ($tplId != '') {
				$db->createCommand()
					->update('vtiger_dataaccess', ['module_name' => $baseModule, 'summary' => $summary], ['dataaccessid' => $tplId])
					->execute();
				Settings_DataAccess_Module_Model::updateConditions($conditionAll, $tplId);
				Settings_DataAccess_Module_Model::updateConditions($conditionOption, $tplId, false);
			} else {
				$db->createCommand()
					->insert('vtiger_dataaccess', ['module_name' => $baseModule, 'summary' => $summary])
					->execute();
				$tplId = $db->getLastInsertID('vtiger_dataaccess_dataaccessid_seq');
				Settings_DataAccess_Module_Model::addConditions($conditionAll, $tplId);
				Settings_DataAccess_Module_Model::addConditions($conditionOption, $tplId, false);
			}
		}

		$DataAccess = Settings_DataAccess_Module_Model::getDataAccessInfo($tplId, false);
		$viewer = $this->getViewer($request);
		$viewer->assign('STEP', 3);
		$viewer->assign('TPL_ID', $tplId);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('REQUEST', $request);
		$viewer->assign('BASE_MODULE', $baseModule);
		$viewer->assign('ACTIONS_SELECTED', $DataAccess['basic_info']['data']);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

		echo $viewer->view('Step3.tpl', $qualifiedModuleName, true);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.Conditions"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}

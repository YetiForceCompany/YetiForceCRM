<?php

/**
 * Settings DataAccess Step3 view  class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
Class Settings_DataAccess_Step3_View extends Settings_Vtiger_Index_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(\App\Request $request)
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

	public function getFooterScripts(\App\Request $request)
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

<?php

/**
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_RecordAllocation_SaveAjax_Action extends Settings_Vtiger_Index_Action
{

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('removePanel');
	}

	function save(Vtiger_Request $request)
	{
		$data = $request->get('param');
		$qualifiedModuleName = $request->getModule(false);

		$moduleInstance = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$moduleInstance->set('type', $data['type']);
		$moduleInstance->saveRecordAllocation(array_filter($data));

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult(true);
		$responceToEmit->emit();
	}

	function removePanel(Vtiger_Request $request)
	{
		$data = $request->get('param');
		$moduleName = $data['module'];
		$toLowerModule = strtolower($moduleName);
		$qualifiedModuleName = $request->getModule(false);

		$moduleInstance = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$moduleInstance->set('type', $data['type']);
		$content = $moduleInstance->removeDataInFile($toLowerModule);
		$moduleInstance->putData($toLowerModule, [], $content);

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult(true);
		$responceToEmit->emit();
	}
}

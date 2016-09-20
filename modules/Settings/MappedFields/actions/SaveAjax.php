<?php

/**
 * SaveAjax Action Class for MappedFields Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
		$this->exposeMethod('import');
	}

	public function step1(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$params = $request->get('param');
		$recordId = $params['record'];
		$step = $params['step'];

		if ($recordId) {
			$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);
		} else {
			$moduleInstance = Settings_MappedFields_Module_Model::getCleanInstance();
		}
		$stepFields = Settings_MappedFields_Module_Model::getFieldsByStep($step);
		foreach ($stepFields as $field) {
			$moduleInstance->getRecord()->set($field, $params[$field]);
			if ($field == 'conditions') {
				$moduleInstance->transformAdvanceFilterToWorkFlowFilter();
			}
		}
		if (!$recordId && $moduleInstance->importsAllowed() >= 1) {
			$message = 'LBL_TEMPATE_EXIST';
		} else {
			$moduleInstance->save();
		}

		$response = new Vtiger_Response();
		$response->setResult(['id' => $moduleInstance->getRecordId(), 'message' => vtranslate($message, $qualifiedModuleName)]);
		$response->emit();
	}

	public function step2(Vtiger_Request $request)
	{
		$params = $request->get('param');
		$recordId = $params['record'];

		$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);
		$moduleInstance->getRecord()->set('params', $params['otherConditions']);
		$moduleInstance->setMapping($params['mapping']);
		$moduleInstance->save(true);

		$response = new Vtiger_Response();
		$response->setResult(['id' => $moduleInstance->getRecordId()]);
		$response->emit();
	}

	public function import(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleInstance = Settings_MappedFields_Module_Model::getCleanInstance();
		$result = $moduleInstance->import($qualifiedModuleName);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

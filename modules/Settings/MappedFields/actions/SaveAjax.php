<?php

/**
 * SaveAjax Action Class for MappedFields Settings.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
		$this->exposeMethod('import');
	}

	public function step1(\App\Request $request)
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
			if ($field === 'conditions') {
				$moduleInstance->transformAdvanceFilterToWorkFlowFilter();
			}
		}
		if (!$recordId && $moduleInstance->importsAllowed() >= 1) {
			$message = 'LBL_TEMPATE_EXIST';
		} else {
			$moduleInstance->save();
		}

		$response = new Vtiger_Response();
		$response->setResult(['id' => $moduleInstance->getRecordId(), 'message' => \App\Language::translate($message, $qualifiedModuleName)]);
		$response->emit();
	}

	public function step2(\App\Request $request)
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

	public function import(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleInstance = Settings_MappedFields_Module_Model::getCleanInstance();
		$result = $moduleInstance->import($qualifiedModuleName);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

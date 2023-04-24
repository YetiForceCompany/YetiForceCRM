<?php

/**
 * SaveAjax Action Class for MappedFields Settings.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	public function step1(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);

		$params = $request->getByType('param', 'Text');
		$step = (int) $params['step'];
		if (3 !== $step) {
			$validators = Settings_MappedFields_Module_Model::$validatorFields;
			$validators['record'] = 'Integer';
			$validators['step'] = 'Integer';
			$params = $request->getMultiDimensionArray('param', $validators);
		}
		$recordId = $params['record'] ?? null;
		if ($recordId) {
			$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);
		} else {
			$moduleInstance = Settings_MappedFields_Module_Model::getCleanInstance();
		}
		$stepFields = Settings_MappedFields_Module_Model::getFieldsByStep($step);
		foreach ($stepFields as $field) {
			$moduleInstance->getRecord()->set($field, $params[$field] ?? null);
			if ('conditions' === $field) {
				$moduleInstance->transformAdvanceFilterToWorkFlowFilter();
			}
		}
		if (!$recordId && $moduleInstance->importsAllowed() >= 1) {
			$message = 'LBL_TEMPATE_EXIST';
		} else {
			$moduleInstance->save();
		}

		$response = new Vtiger_Response();
		$response->setResult(['id' => $moduleInstance->getRecordId(), 'message' => \App\Language::translate($message ?? '', $qualifiedModuleName)]);
		$response->emit();
	}

	public function step2(App\Request $request)
	{
		$params = $request->getMultiDimensionArray('param', [
			'mapping' => [
				[
					'source' => 'Alnum',
					'type' => 'Standard',
					'target' => 'Alnum',
					'default' => 'Text'
				]
			],
			'otherConditions' => 'Text',
			'record' => 'Integer'
		]);
		$recordId = $params['record'];
		$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);
		$moduleInstance->getRecord()->set('params', $params['otherConditions']);
		if (!empty($params['mapping'])) {
			$moduleInstance->setMapping($params['mapping']);
		}
		$moduleInstance->save(true);

		$response = new Vtiger_Response();
		$response->setResult(['id' => $moduleInstance->getRecordId()]);
		$response->emit();
	}

	public function import(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleInstance = Settings_MappedFields_Module_Model::getCleanInstance();
		$result = $moduleInstance->import($qualifiedModuleName);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

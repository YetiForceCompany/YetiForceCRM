<?php

/**
 * Settings MarketingProcesses SaveAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_MarketingProcesses_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateConfig');
	}

	public function updateConfig(\App\Request $request)
	{
		$type = $request->getByType('type', 2);
		$param = $request->getByType('param');
		$value = ($type === 'conversion' && $param === 'mapping') ? \App\Json::encode($request->getArray('value', 2)) : $request->getByType('value', 2);
		$moduleModel = Settings_MarketingProcesses_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $moduleModel->setConfig($param, $type, $value),
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false)),
		]);
		$response->emit();
	}
}

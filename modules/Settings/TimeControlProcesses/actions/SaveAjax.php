<?php

/**
 * Settings TimeControlProcesses SaveAjax action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TimeControlProcesses_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function process(App\Request $request)
	{
		$params = [
			'value' => $request->getBoolean('value') ? 'true' : 'false',
			'type' => $request->getByType('type', 'Alnum'),
			'param' => $request->getByType('param', 'Alnum')
		];
		$moduleModel = Settings_TimeControlProcesses_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $moduleModel->setConfig($params),
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false)),
		]);
		$response->emit();
	}
}

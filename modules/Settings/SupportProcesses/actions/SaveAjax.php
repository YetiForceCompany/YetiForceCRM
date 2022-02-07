<?php

/**
 * Settings SupportProcesses SaveAjax action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_SupportProcesses_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateConfig');
	}

	public function updateConfig(App\Request $request)
	{
		$param = $request->getMultiDimensionArray('param', [
			'param' => 'Alnum',
			'val' => ['Text'],
		]);
		$moduleModel = Settings_SupportProcesses_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $moduleModel->updateTicketStatusNotModify($param),
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false)),
		]);
		$response->emit();
	}
}

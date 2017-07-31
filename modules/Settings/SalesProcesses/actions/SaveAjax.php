<?php

/**
 * Settings SalesProcesses SaveAjax action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_SalesProcesses_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateConfig');
	}

	public function updateConfig(\App\Request $request)
	{
		$param = $request->get('param');
		$moduleModel = Settings_SalesProcesses_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $moduleModel->setConfig($param),
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false))
		));
		$response->emit();
	}
}

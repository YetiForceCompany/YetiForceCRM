<?php

/**
 * Settings TimeControlProcesses SaveAjax action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_TimeControlProcesses_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function process(\App\Request $request)
	{
		$params = $request->get('param');
		$moduleModel = Settings_TimeControlProcesses_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $moduleModel->setConfig($params),
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false))
		));
		$response->emit();
	}
}

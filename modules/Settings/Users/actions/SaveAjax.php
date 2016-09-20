<?php

/**
 * Basic Users Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Users_SaveAjax_Action extends Settings_Vtiger_Save_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateConfig');
		$this->exposeMethod('saveSwitchUsers');
		$this->exposeMethod('saveLocks');
	}

	public function updateConfig(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$recordModel = Settings_Users_Module_Model::getInstance();
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $recordModel->setConfig($param),
			'message' => vtranslate('LBL_SAVE_CONFIG', $request->getModule(false))
		));
		$response->emit();
	}

	public function saveSwitchUsers(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$moduleModel = Settings_Users_Module_Model::getInstance();
		$moduleModel->saveSwitchUsers($param);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'message' => vtranslate('LBL_SAVE_CONFIG', $request->getModule(false))
		));
		$response->emit();
	}

	public function saveLocks(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$moduleModel = Settings_Users_Module_Model::getInstance();
		$moduleModel->saveLocks($param);

		$response = new Vtiger_Response();
		$response->setResult(array(
			'message' => vtranslate('LBL_SAVE_CONFIG', $request->getModule(false))
		));
		$response->emit();
	}
}

<?php

/**
 * Basic Users Action Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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

	public function updateConfig(\App\Request $request)
	{
		$param = $request->get('param');
		$recordModel = Settings_Users_Module_Model::getInstance();
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $recordModel->setConfig($param),
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false))
		));
		$response->emit();
	}

	public function saveSwitchUsers(\App\Request $request)
	{
		$param = $request->get('param');
		$moduleModel = Settings_Users_Module_Model::getInstance();
		$moduleModel->saveSwitchUsers($param);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false))
		));
		$response->emit();
	}

	/**
	 * Action to save locks
	 * @param \App\Request $request
	 */
	public function saveLocks(\App\Request $request)
	{
		$param = $request->get('param');
		$moduleModel = Settings_Users_Module_Model::getInstance();
		$moduleModel->saveLocks($param);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false))
		]);
		$response->emit();
	}
}

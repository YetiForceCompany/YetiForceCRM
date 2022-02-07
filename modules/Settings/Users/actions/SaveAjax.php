<?php

/**
 * Basic Users Action Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Users_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateConfig');
		$this->exposeMethod('saveSwitchUsers');
		$this->exposeMethod('saveLocks');
	}

	public function updateConfig(App\Request $request)
	{
		$recordModel = Settings_Users_Module_Model::getInstance();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $recordModel->setConfig($request->getMultiDimensionArray('param', [
				'type' => 'Standard',
				'param' => 'Standard',
				'val' => 'Text'
			])),
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function saveSwitchUsers(App\Request $request)
	{
		$moduleModel = Settings_Users_Module_Model::getInstance();
		$moduleModel->saveSwitchUsers($request->getMultiDimensionArray('param', [[
			'user' => 'Alnum',
			'access' => 'Alnum'
		]]));
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Action to save locks.
	 *
	 * @param \App\Request $request
	 */
	public function saveLocks(App\Request $request)
	{
		Settings_Users_Module_Model::getInstance()->saveLocks($request->getMultiDimensionArray('param', [[
			'user' => 'Alnum',
			'locks' => 'Standard'
		]]));
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false)),
		]);
		$response->emit();
	}
}

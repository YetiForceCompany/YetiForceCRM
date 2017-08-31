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
		$this->exposeMethod('updateColor');
		$this->exposeMethod('removeColor');
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

	public function updateColor(\App\Request $request)
	{
		$id = $request->getInteger('id');
		$color = $request->get('color');
		if (!$color) {
			$color = \App\Colors::getRandomColor();
		}
		\App\Colors::updateUserColor($id, $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	public function removeColor(\App\Request $request)
	{
		\App\Colors::updateUserColor($request->getInteger('id'), '');
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false))
		));
		$response->emit();
	}
}

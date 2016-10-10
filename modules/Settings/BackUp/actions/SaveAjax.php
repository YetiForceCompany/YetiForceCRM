<?php

/**
 * @package YetiForce.actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_BackUp_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateUsersForNotifications');
		$this->exposeMethod('updateSettings');
	}

	public function updateUsersForNotifications(Vtiger_Request $request)
	{
		$params = $request->get('selectedUsers');
		Settings_BackUp_Module_Model::updateUsersForNotifications($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => vtranslate('LBL_SAVE_CHANGES', $request->getModule(false))
		));
		$response->emit();
	}

	public function updateSettings(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$moduleModel = Settings_BackUp_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $moduleModel->updateSettings($param),
			'message' => vtranslate('LBL_SAVE_CONFIG', $request->getModule(false))
		));
		$response->emit();
	}
}

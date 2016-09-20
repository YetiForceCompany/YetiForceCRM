<?php

/**
 * @package YetiForce.actions
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_CurrencyUpdate_SaveActiveBank_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$id = $request->get('id');
		$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();

		if (!$moduleModel->setActiveBankById($id)) {
			$return = array('success' => false, 'message' => vtranslate('LBL_SET_BANK_ERROR', 'Settings:CurrencyUpdate'));
		} else {
			$return = array('success' => true, 'message' => vtranslate('LBL_SET_BANK_OK', 'Settings:CurrencyUpdate'));
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}

<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

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

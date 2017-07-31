<?php

/**
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_CurrencyUpdate_SaveActiveBank_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	public function process(\App\Request $request)
	{
		$id = $request->get('id');
		$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();

		if (!$moduleModel->setActiveBankById($id)) {
			$return = array('success' => false, 'message' => \App\Language::translate('LBL_SET_BANK_ERROR', 'Settings:CurrencyUpdate'));
		} else {
			$return = array('success' => true, 'message' => \App\Language::translate('LBL_SET_BANK_OK', 'Settings:CurrencyUpdate'));
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}

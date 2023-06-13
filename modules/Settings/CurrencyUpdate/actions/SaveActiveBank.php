<?php

/**
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_CurrencyUpdate_SaveActiveBank_Action extends Settings_Vtiger_Basic_Action
{
	public function process(App\Request $request)
	{
		$id = $request->getInteger('id');
		$qualifiedModule = $request->getModule(false);
		$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();

		if (!$moduleModel->setActiveBankById($id)) {
			$return = ['success' => false, 'message' => \App\Language::translate('LBL_SET_BANK_ERROR', $qualifiedModule)];
		} else {
			$return = ['success' => true, 'message' => \App\Language::translate('LBL_SET_BANK_OK', $qualifiedModule)];
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}

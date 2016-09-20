<?php

/**
 * @package YetiForce.actions
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_CurrencyUpdate_GetBankCurrencies_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		$name = 'Settings_CurrencyUpdate_models_' . $request->get('name') . '_BankModel';
		$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();

		if ($mode == 'supported') {
			$supported = $moduleModel->getSupportedCurrencies($name);
			$response->setResult($supported);
		} else {
			$unsupported = $moduleModel->getUnSupportedCurrencies($name);
			$response->setResult($unsupported);
		}

		$response->emit();
	}
}

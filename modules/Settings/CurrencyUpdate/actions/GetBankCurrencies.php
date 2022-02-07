<?php

/**
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_CurrencyUpdate_GetBankCurrencies_Action extends Settings_Vtiger_Basic_Action
{
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		$name = 'Settings_CurrencyUpdate_' . $request->getByType('name') . '_BankModel';
		$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();

		if ('supported' === $mode) {
			$supported = $moduleModel->getSupportedCurrencies($name);
			$response->setResult($supported);
		} else {
			$unsupported = $moduleModel->getUnSupportedCurrencies($name);
			$response->setResult($unsupported);
		}

		$response->emit();
	}
}

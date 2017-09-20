<?php

/**
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_CurrencyUpdate_GetBankCurrencies_Action extends Settings_Vtiger_Basic_Action
{

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		$name = 'Settings_CurrencyUpdate_Models_' . $request->get('name') . '_BankModel';
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

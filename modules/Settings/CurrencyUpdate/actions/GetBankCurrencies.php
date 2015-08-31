<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Settings_CurrencyUpdate_GetBankCurrencies_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		$name = $request->get('name');
		$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();
		$html = '';

		if ($mode == 'supported') {
			$supported = $moduleModel->getSupportedCurrencies($name);
			foreach ($supported as $name => $code) {
				$html .= '<p><strong>' . vtranslate($name, 'Settings:Currency') . '</strong> - ' . $code . '</p>';
			}
		} else {
			$unsupported = $moduleModel->getUnSupportedCurrencies($name);
			foreach ($unsupported as $name => $code) {
				$html .= '<p><strong>' . vtranslate($name, 'Settings:Currency') . '</strong> - ' . $code . '</p>';
			}
		}
		$response->setResult($html);
		$response->emit();
	}
}

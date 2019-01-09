<?php

/**
 * CheckLanguage Action Class for LangManagement Settings.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_LangManagement_CheckLanguage_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$languageTag = $request->getByType('prefix', 1);
		$data = Settings_LangManagement_Module_Model::isCorrectIETF($languageTag);
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}

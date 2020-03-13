<?php

/**
 * Settings ApiAddress ValidateConfiguration action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_ApiAddress_ValidateConfiguration_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$result = \App\Map\Address::getInstance($request->getByType('provider', 'Text'))->validate();
		if ($result) {
			$result = ['success' => true, 'type' => 'success', 'message' => \App\Language::translate('LBL_PROVIDER_VALID', $moduleName)];
		} else {
			$result = ['success' => false, 'type' => 'error', 'message' => \App\Language::translate('LBL_PROVIDER_INVALID', $moduleName)];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

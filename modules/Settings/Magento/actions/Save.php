<?php

/**
 * YetiForce magento save action class.
 *
 * @package Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_Magento_Save_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * Process request.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function process(App\Request $request)
	{
		$configFile = new \App\ConfigFile('component', $request->getModule(true));
		$result = ['success' => true, 'message' => \App\Language::translate('LBL_SAVED', $request->getModule(false))];
		try {
			foreach (Settings_Magento_Module_Model::getFormFields() as $fieldName => $fieldInfo) {
				$configFile->set($fieldName, $request->getRaw($fieldName));
			}
			$configFile->create();
		} catch (\Throwable $e) {
			$result = ['success' => false, 'message' => \App\Language::translate('LBL_VALUE_NOT_ALLOWED', $request->getModule(false))];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

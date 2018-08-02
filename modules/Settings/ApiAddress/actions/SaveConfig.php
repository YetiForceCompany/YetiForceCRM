<?php

/**
 * Settings ApiAddress SaveConfig action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_ApiAddress_SaveConfig_Action extends Settings_Vtiger_Basic_Action
{
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$result = Settings_ApiAddress_Module_Model::getInstance($moduleName)->setConfig($request->getArray('elements', 'Alnum'));

		if ($result) {
			$result = ['success' => true, 'message' => \App\Language::translate('LBL_SAVE_NOTIFY_OK', $moduleName)];
		} else {
			$result = ['success' => false, 'message' => \App\Language::translate('JS_ERROR', $moduleName)];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

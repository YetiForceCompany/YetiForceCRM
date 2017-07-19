<?php

/**
 * Settings ApiAddress SaveConfig action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_ApiAddress_SaveConfig_Action extends Settings_Vtiger_Basic_Action
{

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$elements = $request->get('elements');

		$result = Settings_ApiAddress_Module_Model::getInstance($moduleName)->setConfig($elements);

		if ($result)
			$result = array('success' => true, 'message' => \App\Language::translate('LBL_SAVE_NOTIFY_OK', $moduleName));
		else
			$result = array('success' => false, 'message' => \App\Language::translate('JS_ERROR', $moduleName));

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}

<?php

/**
 * Settings RecordCollector SaveConfig action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

/**
 * Settings RecordCollector SaveConfig action class.
 */
class Settings_RecordCollector_SaveConfig_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$result = Settings_RecordCollector_Module_Model::getInstance($moduleName);

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

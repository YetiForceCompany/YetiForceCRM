<?php

/**
 * Settings Password save action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_Password_Save_Action extends Settings_Vtiger_Index_Action
{

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$type = $request->get('type');
		$vale = $request->get('vale');
		if (Settings_Password_Record_Model::validation($type, $vale)) {
			Settings_Password_Record_Model::setPassDetail($type, $vale);
			$resp = \App\Language::translate('LBL_SAVE_OK', $moduleName);
		} else {
			$resp = \App\Language::translate('LBL_ERROR', $moduleName);
		}
		$response = new Vtiger_Response();
		$response->setResult($resp);
		$response->emit();
	}
}
